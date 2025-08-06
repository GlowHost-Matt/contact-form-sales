#!/usr/bin/env node
/**
 * 🔒 BULLETPROOF Version Sync Script
 *
 * Enhanced version sync with bulletproof features:
 * - Pre-sync validation
 * - Post-sync verification
 * - Automatic rollback on failure
 * - Detailed logging of all changes
 * - Backup creation before changes
 */

const fs = require('fs');
const path = require('path');

// ANSI color codes
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

class BulletproofVersionSync {
    constructor(options = {}) {
        this.options = {
            dryRun: options.dryRun || false,
            skipBackup: options.skipBackup || false,
            verbose: options.verbose || false,
            ...options
        };

        this.masterVersion = this.getMasterVersion();
        this.changes = [];
        this.backups = [];
        this.errors = [];
    }

    log(message, color = 'reset') {
        const prefix = this.options.dryRun ? '[DRY RUN] ' : '';
        console.log(`${colors[color]}${prefix}${message}${colors.reset}`);
    }

    verbose(message) {
        if (this.options.verbose) {
            this.log(`  🔍 ${message}`, 'blue');
        }
    }

    error(message) {
        this.errors.push(message);
        this.log(`❌ ERROR: ${message}`, 'red');
    }

    success(message) {
        this.log(`✅ ${message}`, 'green');
    }

    warning(message) {
        this.log(`⚠️  WARNING: ${message}`, 'yellow');
    }

    info(message) {
        this.log(`ℹ️  ${message}`, 'cyan');
    }

    getMasterVersion() {
        try {
            const packageJsonPath = path.join(__dirname, '../package.json');
            const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));

            // Validate version format
            const versionRegex = /^\d+\.\d+\.\d+$/;
            if (!versionRegex.test(packageJson.version)) {
                throw new Error(`Invalid version format in package.json: ${packageJson.version}`);
            }

            return packageJson.version;
        } catch (error) {
            throw new Error(`Failed to read master version from package.json: ${error.message}`);
        }
    }

    createBackup(filePath) {
        if (this.options.skipBackup || this.options.dryRun) {
            return null;
        }

        if (!fs.existsSync(filePath)) {
            this.verbose(`File doesn't exist, skipping backup: ${filePath}`);
            return null;
        }

        try {
            const backupPath = `${filePath}.backup.${Date.now()}`;
            fs.copyFileSync(filePath, backupPath);
            this.backups.push({ original: filePath, backup: backupPath });
            this.verbose(`Created backup: ${backupPath}`);
            return backupPath;
        } catch (error) {
            this.error(`Failed to create backup for ${filePath}: ${error.message}`);
            return null;
        }
    }

    restoreBackups() {
        this.log('\n🔄 Rolling back changes...', 'yellow');

        for (const { original, backup } of this.backups) {
            try {
                if (fs.existsSync(backup)) {
                    fs.copyFileSync(backup, original);
                    fs.unlinkSync(backup);
                    this.log(`  Restored: ${original}`, 'yellow');
                } else {
                    this.warning(`Backup not found: ${backup}`);
                }
            } catch (error) {
                this.error(`Failed to restore ${original}: ${error.message}`);
            }
        }

        this.backups = [];
    }

    cleanupBackups() {
        if (this.options.dryRun) return;

        for (const { backup } of this.backups) {
            try {
                if (fs.existsSync(backup)) {
                    fs.unlinkSync(backup);
                    this.verbose(`Cleaned up backup: ${backup}`);
                }
            } catch (error) {
                this.warning(`Failed to cleanup backup ${backup}: ${error.message}`);
            }
        }

        this.backups = [];
    }

    updateFile(filePath, updateFunction, description) {
        this.log(`\n🔄 Updating ${description}...`, 'cyan');

        if (!fs.existsSync(filePath)) {
            this.error(`File not found: ${filePath}`);
            return false;
        }

        // Create backup
        const backupPath = this.createBackup(filePath);

        try {
            // Read current content
            const originalContent = fs.readFileSync(filePath, 'utf8');
            this.verbose(`Read ${originalContent.length} characters from ${filePath}`);

            // Apply update
            const updatedContent = updateFunction(originalContent);

            // Check if content actually changed
            if (originalContent === updatedContent) {
                this.warning(`No changes needed for ${description} (already correct)`);
                return true;
            }

            // Write updated content (unless dry run)
            if (!this.options.dryRun) {
                fs.writeFileSync(filePath, updatedContent);
            }

            // Log the change
            const change = {
                file: filePath,
                description,
                backup: backupPath,
                originalLength: originalContent.length,
                updatedLength: updatedContent.length,
                timestamp: new Date().toISOString()
            };
            this.changes.push(change);

            this.success(`Updated ${description} to v${this.masterVersion}`);
            this.verbose(`Content length: ${originalContent.length} → ${updatedContent.length}`);

            return true;
        } catch (error) {
            this.error(`Failed to update ${description}: ${error.message}`);
            return false;
        }
    }

    updateInstallerPHP() {
        const filePath = path.join(__dirname, '../installer.php');

        return this.updateFile(filePath, (content) => {
            let updatedContent = content;

            // Update INSTALLER_VERSION constant
            const versionConstantRegex = /define\('INSTALLER_VERSION',\s*'[^']+'\);/;
            const newVersionConstant = `define('INSTALLER_VERSION', '${this.masterVersion}');`;

            if (versionConstantRegex.test(updatedContent)) {
                updatedContent = updatedContent.replace(versionConstantRegex, newVersionConstant);
                this.verbose('Updated INSTALLER_VERSION constant');
            } else {
                this.warning('INSTALLER_VERSION constant not found in expected format');
            }

            // Update any other version references in user agent strings, etc.
            const userAgentRegex = /GlowHost-Contact-Form-Installer\/[^\s'"]+/g;
            updatedContent = updatedContent.replace(userAgentRegex, `GlowHost-Contact-Form-Installer/${this.masterVersion}`);

            return updatedContent;
        }, 'installer.php INSTALLER_VERSION');
    }

    updateContactFormFooter() {
        const filePath = path.join(__dirname, '../src/components/layout/MainLayout.tsx');

        return this.updateFile(filePath, (content) => {
            // Update hardcoded version number in footer
            const versionRegex = /Sales Contact Form Version:\s*[0-9]+(?:\.[0-9]+)*(?:\.[0-9]+)*/;
            const newVersionString = `Sales Contact Form Version: ${this.masterVersion}`;

            if (versionRegex.test(content)) {
                const updatedContent = content.replace(versionRegex, newVersionString);
                this.verbose('Updated footer version display');
                return updatedContent;
            } else {
                this.warning('Footer version pattern not found in expected format');
                return content;
            }
        }, 'MainLayout.tsx footer version');
    }

    updateDeploymentPackageReferences() {
        // Update any references to deployment package names
        const packageJsonPath = path.join(__dirname, '../package.json');

        return this.updateFile(packageJsonPath, (content) => {
            const packageJson = JSON.parse(content);

            // Update any package-specific fields that might reference version
            let changed = false;

            // Example: Update custom fields if they exist
            if (packageJson.deploymentPackage) {
                packageJson.deploymentPackage = `contact-form-v${this.masterVersion}-deployment.zip`;
                changed = true;
            }

            if (packageJson.releaseTag) {
                packageJson.releaseTag = `v${this.masterVersion}`;
                changed = true;
            }

            return changed ? JSON.stringify(packageJson, null, 2) + '\n' : content;
        }, 'package.json deployment references');
    }

    validateChanges() {
        this.log('\n🔍 Validating changes...', 'cyan');

        try {
            // Use our bulletproof validator to check if sync worked
            const VersionValidator = require('./validate-versions.js');
            const validator = new VersionValidator();

            // Check each file individually for better error reporting
            let allValid = true;

            // Check installer.php
            if (fs.existsSync('installer.php')) {
                const installerContent = fs.readFileSync('installer.php', 'utf8');
                const installerVersionMatch = installerContent.match(/define\('INSTALLER_VERSION',\s*'([^']+)'\);/);

                if (installerVersionMatch && installerVersionMatch[1] === this.masterVersion) {
                    this.success('installer.php version validated');
                } else {
                    this.error(`installer.php version validation failed. Expected: ${this.masterVersion}, Found: ${installerVersionMatch ? installerVersionMatch[1] : 'not found'}`);
                    allValid = false;
                }
            }

            // Check MainLayout.tsx
            const layoutPath = 'src/components/layout/MainLayout.tsx';
            if (fs.existsSync(layoutPath)) {
                const layoutContent = fs.readFileSync(layoutPath, 'utf8');
                const layoutVersionMatch = layoutContent.match(/Sales Contact Form Version:\s*([0-9]+(?:\.[0-9]+)*)/);

                if (layoutVersionMatch && layoutVersionMatch[1] === this.masterVersion) {
                    this.success('MainLayout.tsx version validated');
                } else {
                    this.error(`MainLayout.tsx version validation failed. Expected: ${this.masterVersion}, Found: ${layoutVersionMatch ? layoutVersionMatch[1] : 'not found'}`);
                    allValid = false;
                }
            }

            return allValid;
        } catch (error) {
            this.error(`Validation failed: ${error.message}`);
            return false;
        }
    }

    generateChangeReport() {
        this.log('\n' + '='.repeat(60), 'bold');
        this.log('📋 VERSION SYNC REPORT', 'bold');
        this.log('='.repeat(60), 'bold');

        this.log(`\n🎯 Target Version: ${this.masterVersion}`, 'magenta');
        this.log(`📊 Files Modified: ${this.changes.length}`, 'cyan');
        this.log(`💾 Backups Created: ${this.backups.length}`, 'cyan');
        this.log(`❌ Errors: ${this.errors.length}`, this.errors.length > 0 ? 'red' : 'green');

        if (this.changes.length > 0) {
            this.log('\n📁 Modified Files:', 'yellow');
            for (const change of this.changes) {
                this.log(`  ✓ ${change.description}`, 'green');
                if (this.options.verbose) {
                    this.log(`    File: ${change.file}`, 'blue');
                    this.log(`    Size: ${change.originalLength} → ${change.updatedLength} chars`, 'blue');
                    this.log(`    Time: ${change.timestamp}`, 'blue');
                    if (change.backup) {
                        this.log(`    Backup: ${change.backup}`, 'blue');
                    }
                }
            }
        }

        if (this.errors.length > 0) {
            this.log('\n❌ Errors Encountered:', 'red');
            for (const error of this.errors) {
                this.log(`  • ${error}`, 'red');
            }
        }

        this.log('', 'reset');
    }

    async run() {
        this.log('🔒 BULLETPROOF VERSION SYNC STARTING...', 'bold');
        this.log(`🎯 Master version: ${this.masterVersion}`, 'cyan');

        if (this.options.dryRun) {
            this.warning('DRY RUN MODE - No files will be modified');
        }

        try {
            // Execute all updates
            const updates = [
                () => this.updateInstallerPHP(),
                () => this.updateContactFormFooter(),
                () => this.updateDeploymentPackageReferences()
            ];

            let allSucceeded = true;
            for (const update of updates) {
                if (!update()) {
                    allSucceeded = false;
                }
            }

            if (!allSucceeded) {
                this.error('Some updates failed');
                this.restoreBackups();
                this.generateChangeReport();
                process.exit(1);
            }

            // Validate changes
            if (!this.options.dryRun && !this.validateChanges()) {
                this.error('Validation failed after sync');
                this.restoreBackups();
                this.generateChangeReport();
                process.exit(1);
            }

            // Success!
            this.generateChangeReport();

            if (this.errors.length === 0) {
                this.log('🎉 VERSION SYNC COMPLETED SUCCESSFULLY!', 'green');
                this.log(`✨ All components now use version ${this.masterVersion}`, 'green');

                if (!this.options.dryRun) {
                    this.cleanupBackups();
                }
            } else {
                this.error('Version sync completed with errors');
                process.exit(1);
            }

        } catch (error) {
            this.error(`Fatal error during version sync: ${error.message}`);
            this.restoreBackups();
            process.exit(1);
        }
    }
}

// CLI handling
if (require.main === module) {
    const args = process.argv.slice(2);
    const options = {};

    for (const arg of args) {
        switch (arg) {
            case '--dry-run':
                options.dryRun = true;
                break;
            case '--skip-backup':
                options.skipBackup = true;
                break;
            case '--verbose':
            case '-v':
                options.verbose = true;
                break;
            case '--help':
                console.log(`
🔒 Bulletproof Version Sync

Usage: node scripts/version-sync.js [options]

Options:
  --dry-run       Show what would be changed without making changes
  --skip-backup   Don't create backup files (not recommended)
  --verbose, -v   Show detailed logging
  --help          Show this help

Examples:
  node scripts/version-sync.js --dry-run
  node scripts/version-sync.js --verbose
  bun run version-sync
`);
                process.exit(0);
        }
    }

    const sync = new BulletproofVersionSync(options);
    sync.run().catch(error => {
        console.error(`Fatal error: ${error.message}`);
        process.exit(1);
    });
}

module.exports = BulletproofVersionSync;
