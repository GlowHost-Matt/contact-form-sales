#!/usr/bin/env node
/**
 * 🔒 BULLETPROOF Version Validation Script
 *
 * This script validates that ALL version references across the entire project
 * match the master version in package.json. It's designed to catch ANY
 * version drift and prevent it from being deployed.
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// ANSI color codes for output
const colors = {
    red: '\x1b[31m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    magenta: '\x1b[35m',
    cyan: '\x1b[36m',
    reset: '\x1b[0m',
    bold: '\x1b[1m'
};

class VersionValidator {
    constructor() {
        this.errors = [];
        this.warnings = [];
        this.masterVersion = this.getMasterVersion();
        this.checksPassed = 0;
        this.totalChecks = 0;
    }

    log(message, color = 'reset') {
        console.log(`${colors[color]}${message}${colors.reset}`);
    }

    error(message) {
        this.errors.push(message);
        this.log(`❌ ERROR: ${message}`, 'red');
    }

    warning(message) {
        this.warnings.push(message);
        this.log(`⚠️  WARNING: ${message}`, 'yellow');
    }

    success(message) {
        this.checksPassed++;
        this.log(`✅ ${message}`, 'green');
    }

    getMasterVersion() {
        try {
            const packageJsonPath = path.join(__dirname, '../package.json');
            const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
            return packageJson.version;
        } catch (error) {
            throw new Error(`Failed to read master version from package.json: ${error.message}`);
        }
    }

    checkFile(filePath, patterns, description) {
        this.totalChecks++;

        if (!fs.existsSync(filePath)) {
            this.warning(`${description} file not found: ${filePath}`);
            return false;
        }

        try {
            const content = fs.readFileSync(filePath, 'utf8');
            let hasIssues = false;

            for (const pattern of patterns) {
                const matches = content.match(pattern.regex);
                if (matches) {
                    const foundVersion = matches[pattern.versionGroup || 1];
                    if (foundVersion !== this.masterVersion) {
                        this.error(`${description} version mismatch in ${filePath}:
  Expected: ${this.masterVersion}
  Found: ${foundVersion}
  Pattern: ${pattern.description || 'Version check'}
  Match: ${matches[0]}`);
                        hasIssues = true;
                    }
                } else if (pattern.required) {
                    this.error(`${description} missing required version pattern in ${filePath}:
  Pattern: ${pattern.description || 'Required version check'}
  Regex: ${pattern.regex}`);
                    hasIssues = true;
                }
            }

            if (!hasIssues) {
                this.success(`${description} version is correct: ${this.masterVersion}`);
                return true;
            }
        } catch (error) {
            this.error(`Failed to check ${description}: ${error.message}`);
        }

        return false;
    }

    validateInstallerPHP() {
        this.log('\n🔍 Checking installer.php...', 'cyan');

        const patterns = [
            {
                regex: /define\('INSTALLER_VERSION',\s*'([^']+)'\);/,
                description: 'INSTALLER_VERSION constant',
                required: true
            },
            {
                regex: /GlowHost-Contact-Form-Installer\/([^\s']+)/g,
                description: 'User-Agent version string'
            }
        ];

        return this.checkFile('installer.php', patterns, 'installer.php');
    }

    validateMainLayout() {
        this.log('\n🔍 Checking MainLayout.tsx...', 'cyan');

        const patterns = [
            {
                regex: /Sales Contact Form Version:\s*([0-9]+(?:\.[0-9]+)*(?:\.[0-9]+)*)/,
                description: 'Footer version display',
                required: true
            }
        ];

        return this.checkFile('src/components/layout/MainLayout.tsx', patterns, 'MainLayout.tsx');
    }

    validatePackageJSON() {
        this.log('\n🔍 Checking package.json structure...', 'cyan');
        this.totalChecks++;

        try {
            const packageJsonPath = path.join(__dirname, '../package.json');
            const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));

            // Validate version format
            const versionRegex = /^\d+\.\d+\.\d+$/;
            if (!versionRegex.test(packageJson.version)) {
                this.error(`package.json version format invalid: ${packageJson.version}
  Expected format: X.Y.Z (semantic versioning)
  Found: ${packageJson.version}`);
                return false;
            }

            this.success(`package.json version format is valid: ${packageJson.version}`);
            return true;
        } catch (error) {
            this.error(`Failed to validate package.json: ${error.message}`);
            return false;
        }
    }

    validateBuiltFiles() {
        this.log('\n🔍 Checking built files (if they exist)...', 'cyan');

        const buildDirs = ['out', 'dist', '.next'];
        let foundBuiltFiles = false;

        for (const dir of buildDirs) {
            if (fs.existsSync(dir)) {
                foundBuiltFiles = true;
                this.log(`  Scanning ${dir}/ for version references...`, 'blue');

                try {
                    const result = execSync(`find ${dir} -type f \\( -name "*.html" -o -name "*.js" -o -name "*.css" \\) -exec grep -l "version\\|Version" {} + 2>/dev/null || true`, { encoding: 'utf8' });

                    if (result.trim()) {
                        const files = result.trim().split('\n');
                        for (const file of files) {
                            this.totalChecks++;

                            const content = fs.readFileSync(file, 'utf8');

                            // Look for potential version mismatches
                            const versionMatches = content.match(/\d+\.\d+\.\d+/g);
                            if (versionMatches) {
                                const otherVersions = versionMatches.filter(v => v !== this.masterVersion);
                                if (otherVersions.length > 0) {
                                    this.warning(`Built file ${file} contains other version numbers: ${otherVersions.join(', ')}
  This might be from dependencies, but verify it's not our version`);
                                } else {
                                    this.checksPassed++;
                                }
                            } else {
                                this.checksPassed++;
                            }
                        }
                    }
                } catch (error) {
                    this.warning(`Could not scan ${dir} for version references: ${error.message}`);
                }
            }
        }

        if (!foundBuiltFiles) {
            this.log('  No built files found (run build first to check built files)', 'yellow');
        }
    }

    validateDeploymentPackages() {
        this.log('\n🔍 Checking deployment packages...', 'cyan');

        const deploymentPatterns = [
            'contact-form-deployment.zip',
            'contact-form-v*.zip',
            'deployment-package',
            'deployment-package-v*'
        ];

        for (const pattern of deploymentPatterns) {
            try {
                const matches = execSync(`ls ${pattern} 2>/dev/null || true`, { encoding: 'utf8' }).trim();
                if (matches) {
                    this.totalChecks++;
                    const files = matches.split('\n').filter(f => f);

                    for (const file of files) {
                        if (file.includes(this.masterVersion)) {
                            this.success(`Deployment package version matches: ${file}`);
                        } else {
                            this.warning(`Deployment package may have mismatched version: ${file}
  Expected version ${this.masterVersion} in filename`);
                        }
                    }
                }
            } catch (error) {
                // Ignore errors - deployment packages might not exist
            }
        }
    }

    validateGitTags() {
        this.log('\n🔍 Checking git tags...', 'cyan');

        try {
            const tags = execSync('git tag -l', { encoding: 'utf8' }).trim();
            if (tags) {
                this.totalChecks++;
                const tagList = tags.split('\n');
                const versionTag = `v${this.masterVersion}`;

                if (tagList.includes(versionTag)) {
                    this.success(`Git tag exists for current version: ${versionTag}`);
                } else {
                    this.warning(`No git tag found for current version: ${versionTag}
  Consider creating a tag: git tag ${versionTag}`);
                }
            }
        } catch (error) {
            this.warning(`Could not check git tags: ${error.message}`);
        }
    }

    generateReport() {
        this.log('\n' + '='.repeat(60), 'bold');
        this.log('📊 BULLETPROOF VERSION VALIDATION REPORT', 'bold');
        this.log('='.repeat(60), 'bold');

        this.log(`\n🎯 Master Version: ${this.masterVersion}`, 'magenta');
        this.log(`📈 Checks Passed: ${this.checksPassed}/${this.totalChecks}`, 'cyan');

        if (this.errors.length === 0) {
            this.log('\n🎉 SUCCESS: All version checks passed!', 'green');
            this.log('✨ Version consistency is bulletproof!', 'green');
        } else {
            this.log(`\n❌ FAILED: ${this.errors.length} version consistency errors found`, 'red');
            this.log('\n🔧 REQUIRED ACTIONS:', 'yellow');
            this.log('1. Run: bun run version-sync', 'yellow');
            this.log('2. Manually fix any remaining issues', 'yellow');
            this.log('3. Re-run: bun run validate-versions', 'yellow');
        }

        if (this.warnings.length > 0) {
            this.log(`\n⚠️  ${this.warnings.length} warnings found (review recommended)`, 'yellow');
        }

        this.log('', 'reset');

        return this.errors.length === 0;
    }

    async run() {
        this.log('🔒 BULLETPROOF VERSION VALIDATOR STARTING...', 'bold');
        this.log(`🎯 Master version: ${this.masterVersion}`, 'cyan');

        // Run all validations
        this.validatePackageJSON();
        this.validateInstallerPHP();
        this.validateMainLayout();
        this.validateBuiltFiles();
        this.validateDeploymentPackages();
        this.validateGitTags();

        // Generate final report
        const success = this.generateReport();

        // Exit with appropriate code
        process.exit(success ? 0 : 1);
    }
}

// Run the validator
if (require.main === module) {
    const validator = new VersionValidator();
    validator.run().catch(error => {
        console.error(`Fatal error: ${error.message}`);
        process.exit(1);
    });
}

module.exports = VersionValidator;
