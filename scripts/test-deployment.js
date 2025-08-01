#!/usr/bin/env node

/**
 * GlowHost Contact Form System - Deployment Test Script
 * Validates the deployment workflow and package integrity
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Colors for console output
const colors = {
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  reset: '\x1b[0m',
  bold: '\x1b[1m'
};

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

function logStep(step, description) {
  log(`\n${step} ${description}`, 'bold');
}

function logSuccess(message) {
  log(`✅ ${message}`, 'green');
}

function logError(message) {
  log(`❌ ${message}`, 'red');
}

function logWarning(message) {
  log(`⚠️  ${message}`, 'yellow');
}

function logInfo(message) {
  log(`ℹ️  ${message}`, 'blue');
}

/**
 * Test configuration
 */
const tests = {
  environment: {
    name: "Environment Setup",
    checks: [
      { name: "Node.js version", test: () => checkNodeVersion() },
      { name: "Bun installation", test: () => checkBunInstallation() },
      { name: "Git repository", test: () => checkGitRepo() },
      { name: "Package.json exists", test: () => fs.existsSync('package.json') }
    ]
  },
  
  projectStructure: {
    name: "Project Structure",
    checks: [
      { name: "Next.js config", test: () => checkNextConfig() },
      { name: "API directory", test: () => fs.existsSync('api') && fs.existsSync('api/submit-form.php') },
      { name: "Config directory", test: () => fs.existsSync('config') },
      { name: "Scripts directory", test: () => fs.existsSync('scripts') },
      { name: "GitHub Actions workflow", test: () => fs.existsSync('.github/workflows/deploy.yml') }
    ]
  },
  
  buildProcess: {
    name: "Build Process",
    checks: [
      { name: "Dependencies install", test: () => testDependenciesInstall() },
      { name: "Next.js build", test: () => testNextJsBuild() },
      { name: "Deployment script", test: () => testDeploymentScript() }
    ]
  },
  
  deploymentPackage: {
    name: "Deployment Package",
    checks: [
      { name: "Package structure", test: () => checkDeploymentStructure() },
      { name: "Required files", test: () => checkRequiredFiles() },
      { name: "File permissions", test: () => checkFilePermissions() },
      { name: "Package integrity", test: () => checkPackageIntegrity() }
    ]
  }
};

/**
 * Individual test functions
 */
function checkNodeVersion() {
  try {
    const version = execSync('node --version', { encoding: 'utf8' }).trim();
    const majorVersion = parseInt(version.replace('v', '').split('.')[0]);
    return majorVersion >= 18;
  } catch {
    return false;
  }
}

function checkBunInstallation() {
  try {
    execSync('bun --version', { encoding: 'utf8', stdio: 'ignore' });
    return true;
  } catch {
    logWarning("Bun not installed, falling back to npm");
    return true; // Not critical, npm can be used
  }
}

function checkGitRepo() {
  return fs.existsSync('.git');
}

function checkNextConfig() {
  if (!fs.existsSync('next.config.js')) return false;
  
  const content = fs.readFileSync('next.config.js', 'utf8');
  return content.includes("output: 'export'") && content.includes("distDir: 'out'");
}

function testDependenciesInstall() {
  try {
    logInfo("Installing dependencies...");
    execSync('bun install --frozen-lockfile || npm ci', { 
      encoding: 'utf8', 
      stdio: 'pipe' 
    });
    return fs.existsSync('node_modules');
  } catch (error) {
    logError(`Dependencies install failed: ${error.message}`);
    return false;
  }
}

function testNextJsBuild() {
  try {
    logInfo("Building Next.js application...");
    execSync('bun run build || npm run build', { 
      encoding: 'utf8', 
      stdio: 'pipe' 
    });
    return fs.existsSync('out') && fs.existsSync('out/index.html');
  } catch (error) {
    logError(`Next.js build failed: ${error.message}`);
    return false;
  }
}

function testDeploymentScript() {
  try {
    logInfo("Running deployment script...");
    execSync('node scripts/build-deployment.js', { 
      encoding: 'utf8', 
      stdio: 'pipe' 
    });
    return fs.existsSync('deployment-package');
  } catch (error) {
    logError(`Deployment script failed: ${error.message}`);
    return false;
  }
}

function checkDeploymentStructure() {
  const requiredPaths = [
    'deployment-package/index.html',
    'deployment-package/_next',
    'deployment-package/api',
    'deployment-package/config',
    'deployment-package/.htaccess'
  ];
  
  return requiredPaths.every(path => fs.existsSync(path));
}

function checkRequiredFiles() {
  const requiredFiles = [
    'deployment-package/index.html',
    'deployment-package/api/submit-form.php',
    'deployment-package/.htaccess',
    'deployment-package/deployment-manifest.json'
  ];
  
  return requiredFiles.every(file => {
    const exists = fs.existsSync(file);
    if (!exists) logError(`Missing required file: ${file}`);
    return exists;
  });
}

function checkFilePermissions() {
  // Check if files have reasonable sizes (not empty)
  const filesToCheck = [
    'deployment-package/index.html',
    'deployment-package/.htaccess',
    'deployment-package/api/submit-form.php'
  ];
  
  return filesToCheck.every(file => {
    if (!fs.existsSync(file)) return false;
    const stat = fs.statSync(file);
    return stat.size > 0;
  });
}

function checkPackageIntegrity() {
  try {
    // Check if deployment manifest exists and is valid JSON
    if (!fs.existsSync('deployment-package/deployment-manifest.json')) {
      return false;
    }
    
    const manifest = JSON.parse(
      fs.readFileSync('deployment-package/deployment-manifest.json', 'utf8')
    );
    
    return manifest.name && manifest.buildTime && manifest.files;
  } catch {
    return false;
  }
}

/**
 * GitHub Actions workflow validation
 */
function validateWorkflow() {
  logStep("🔍", "Validating GitHub Actions Workflow");
  
  const workflowPath = '.github/workflows/deploy.yml';
  if (!fs.existsSync(workflowPath)) {
    logError("GitHub Actions workflow file not found");
    return false;
  }
  
  const workflowContent = fs.readFileSync(workflowPath, 'utf8');
  
  const requiredElements = [
    'name:',
    'on:',
    'jobs:',
    'build-and-package:',
    'steps:',
    'checkout@v4',
    'setup-bun@v1',
    'next build',
    'upload-artifact@v3'
  ];
  
  let valid = true;
  requiredElements.forEach(element => {
    if (!workflowContent.includes(element)) {
      logError(`Workflow missing: ${element}`);
      valid = false;
    }
  });
  
  if (valid) {
    logSuccess("GitHub Actions workflow is valid");
  }
  
  return valid;
}

/**
 * Generate deployment report
 */
function generateReport(results) {
  logStep("📊", "Deployment Readiness Report");
  
  let totalTests = 0;
  let passedTests = 0;
  
  for (const [category, test] of Object.entries(results)) {
    log(`\n${test.name}:`, 'bold');
    
    test.results.forEach(result => {
      totalTests++;
      if (result.passed) {
        passedTests++;
        logSuccess(`${result.name}`);
      } else {
        logError(`${result.name}`);
      }
    });
  }
  
  const percentage = Math.round((passedTests / totalTests) * 100);
  
  log(`\n${'='.repeat(50)}`, 'bold');
  log(`📈 Overall Score: ${passedTests}/${totalTests} (${percentage}%)`, 'bold');
  
  if (percentage >= 90) {
    logSuccess("🎉 Deployment ready! All critical tests passed.");
  } else if (percentage >= 75) {
    logWarning("⚠️  Deployment mostly ready, but some issues detected.");
  } else {
    logError("❌ Deployment not ready. Critical issues need to be resolved.");
  }
  
  // Package information
  if (fs.existsSync('deployment-package')) {
    const packageStats = getPackageStats();
    log(`\n📦 Package Information:`, 'bold');
    logInfo(`Files: ${packageStats.fileCount}`);
    logInfo(`Size: ${packageStats.totalSize}`);
    logInfo(`Location: deployment-package/`);
  }
  
  // Next steps
  log(`\n🚀 Next Steps:`, 'bold');
  if (percentage >= 90) {
    logInfo("1. Commit and push to trigger GitHub Actions");
    logInfo("2. Download deployment package from GitHub Releases");
    logInfo("3. Upload to your shared hosting provider");
    logInfo("4. Configure email settings in api/submit-form.php");
  } else {
    logInfo("1. Fix the failed tests above");
    logInfo("2. Re-run this test script");
    logInfo("3. Proceed with deployment when ready");
  }
}

function getPackageStats() {
  let fileCount = 0;
  let totalSize = 0;
  
  function scanDir(dir) {
    const items = fs.readdirSync(dir);
    
    items.forEach(item => {
      const fullPath = path.join(dir, item);
      const stat = fs.statSync(fullPath);
      
      if (stat.isDirectory()) {
        scanDir(fullPath);
      } else {
        fileCount++;
        totalSize += stat.size;
      }
    });
  }
  
  if (fs.existsSync('deployment-package')) {
    scanDir('deployment-package');
  }
  
  return {
    fileCount,
    totalSize: `${(totalSize / 1024 / 1024).toFixed(2)} MB`
  };
}

/**
 * Cleanup function
 */
function cleanup() {
  logStep("🧹", "Cleaning up test artifacts");
  
  const pathsToClean = [
    'deployment-package',
    'out'
  ];
  
  pathsToClean.forEach(cleanPath => {
    if (fs.existsSync(cleanPath)) {
      fs.rmSync(cleanPath, { recursive: true, force: true });
      logInfo(`Removed ${cleanPath}`);
    }
  });
}

/**
 * Main test execution
 */
async function runTests() {
  log('🚀 GlowHost Contact Form System - Deployment Test Suite\n', 'bold');
  
  const results = {};
  
  // Run all test categories
  for (const [key, testGroup] of Object.entries(tests)) {
    logStep("🔧", `Testing ${testGroup.name}`);
    
    results[key] = {
      name: testGroup.name,
      results: []
    };
    
    for (const check of testGroup.checks) {
      try {
        const passed = check.test();
        results[key].results.push({
          name: check.name,
          passed
        });
        
        if (passed) {
          logSuccess(check.name);
        } else {
          logError(check.name);
        }
      } catch (error) {
        logError(`${check.name}: ${error.message}`);
        results[key].results.push({
          name: check.name,
          passed: false
        });
      }
    }
  }
  
  // Validate workflow
  const workflowValid = validateWorkflow();
  
  // Generate report
  generateReport(results);
  
  // Cleanup unless --keep-artifacts flag is passed
  if (!process.argv.includes('--keep-artifacts')) {
    cleanup();
  } else {
    logInfo("Keeping artifacts (--keep-artifacts flag detected)");
  }
}

// Handle command line arguments
if (process.argv.includes('--help') || process.argv.includes('-h')) {
  console.log(`
GlowHost Contact Form System - Deployment Test Script

Usage: node scripts/test-deployment.js [options]

Options:
  --keep-artifacts    Keep build artifacts after testing
  --help, -h         Show this help message

Examples:
  node scripts/test-deployment.js
  node scripts/test-deployment.js --keep-artifacts
`);
  process.exit(0);
}

// Run tests
if (require.main === module) {
  runTests().catch(error => {
    logError(`Test suite failed: ${error.message}`);
    process.exit(1);
  });
}

module.exports = { runTests, validateWorkflow };