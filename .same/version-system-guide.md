# 🔒 Bulletproof Version Consistency System

> **CRITICAL**: This system prevents ANY version drift across the GlowHost Contact Form project.
> Breaking these safeguards will result in deployment failures and version inconsistencies.

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Safeguards in Place](#safeguards-in-place)
3. [Daily Workflow](#daily-workflow)
4. [Scripts Reference](#scripts-reference)
5. [GitHub Actions](#github-actions)
6. [Troubleshooting](#troubleshooting)
7. [Emergency Procedures](#emergency-procedures)

## System Overview

This bulletproof system ensures that ALL version references across the project stay synchronized with the master version in `package.json`. No manual edit can break version consistency without being caught by automated validation.

### 🎯 Master Version Source
- **Single Source of Truth**: `package.json` version field
- **Format**: Semantic versioning (X.Y.Z)
- **All other files sync to this version**

### 🔍 Files Under Version Control
- `package.json` - Master version
- `installer.php` - INSTALLER_VERSION constant
- `src/components/layout/MainLayout.tsx` - Footer version display
- Built files in `out/` directory
- Deployment packages
- Git tags
- GitHub releases

## Safeguards in Place

### 🛡️ 1. GitHub Actions CI/CD
**Files**: `.github/workflows/version-consistency.yml`, `.github/workflows/release-automation.yml`

- **Triggers**: Every push, PR, and release
- **Actions**: Validates ALL version references
- **Blocks**: Merges if versions are inconsistent
- **Reports**: Detailed validation status

### 🛡️ 2. Pre-build Validation
**Integration**: `package.json` prebuild scripts

- Automatically runs before ANY build
- Prevents inconsistent builds from being created
- Fails fast if versions don't match

### 🛡️ 3. Comprehensive Validation Script
**File**: `scripts/validate-versions.js`

- Checks ALL possible version references
- Provides detailed error messages
- Scans built files for consistency
- Validates deployment packages

### 🛡️ 4. Enhanced Sync Script
**File**: `scripts/version-sync.js`

- Creates backups before changes
- Validates changes after sync
- Automatic rollback on failure
- Detailed logging of all changes

### 🛡️ 5. Release Automation
**File**: `scripts/create-release.js`

- Enforces version validation before release
- Creates consistent deployment packages
- Automates GitHub releases
- Updates installer URLs

## Daily Workflow

### ✅ Making Changes (Safe)

```bash
# 1. Check current version status
bun run version:check

# 2. Make your code changes
# ... edit files ...

# 3. Before committing, validate consistency
bun run validate-versions

# 4. If validation fails, sync versions
bun run version-sync

# 5. Validate again
bun run validate-versions

# 6. Commit your changes
git add .
git commit -m "Your changes"
```

### ⚠️ Changing Version

```bash
# Method 1: Using npm (recommended)
npm version patch  # 2.0.0 → 2.0.1
npm version minor  # 2.0.0 → 2.1.0
npm version major  # 2.0.0 → 3.0.0

# Method 2: Manual (then sync)
# Edit package.json version
bun run version-sync
bun run validate-versions
```

### 🚀 Creating Releases

```bash
# Automated release (recommended)
bun run create-release

# Test release first
bun run create-release:dry-run

# Pre-release
bun run create-release:pre

# Specific version release
bun run release:patch  # Auto-increment and release
bun run release:minor
bun run release:major
```

## Scripts Reference

### 🔍 Validation Scripts

```bash
# Basic validation
bun run validate-versions

# Verbose validation (detailed output)
bun run validate-versions:verbose

# Check current version
bun run version:check

# Complete validation and report
bun run version:validate-all
```

### 🔄 Sync Scripts

```bash
# Standard sync
bun run version-sync

# Test sync (no changes)
bun run version-sync:dry-run

# Verbose sync (detailed logging)
bun run version-sync:verbose

# Sync and validate
bun run version:sync-and-validate
```

### 🚀 Release Scripts

```bash
# Create release
bun run create-release

# Test release creation
bun run create-release:dry-run

# Pre-release
bun run create-release:pre

# Version bump + release
bun run release:patch
bun run release:minor
bun run release:major
```

### 🔧 Development Scripts

```bash
# Pre-commit validation
bun run pre-commit

# Pre-push validation
bun run pre-push

# CI validation
bun run ci:version-check
```

## GitHub Actions

### 🔍 Version Consistency Check
**File**: `.github/workflows/version-consistency.yml`

**Triggers**:
- Push to main/master/develop
- Pull requests
- Manual dispatch

**Actions**:
- Validates all version references
- Tests version sync functionality
- Checks built files for consistency
- Generates detailed reports
- Blocks merge on failure

### 🚀 Release Automation
**File**: `.github/workflows/release-automation.yml`

**Triggers**:
- Git tags (v*)
- Manual dispatch with version input

**Actions**:
- Complete version validation
- Project build
- Deployment package creation
- GitHub release creation
- Final validation

## Troubleshooting

### ❌ "Version consistency check failed"

**Cause**: One or more files have wrong version

**Fix**:
```bash
# 1. Run diagnostic
bun run validate-versions

# 2. See what's wrong and fix it
bun run version-sync

# 3. Verify fix
bun run validate-versions
```

### ❌ "Build failed in CI"

**Cause**: Pre-build validation failed

**Fix**:
```bash
# 1. Check validation locally
bun run validate-versions

# 2. Fix any issues
bun run version-sync

# 3. Test build locally
bun run build

# 4. Commit and push
git add .
git commit -m "Fix version consistency"
git push
```

### ❌ "Validation script not found"

**Cause**: Script files missing or corrupted

**Fix**:
```bash
# 1. Check scripts exist
ls -la scripts/

# 2. Restore from git if needed
git checkout HEAD -- scripts/

# 3. Make scripts executable
chmod +x scripts/*.js
```

### ❌ "MainLayout.tsx version mismatch"

**Cause**: Footer version display is wrong

**Manual Fix**:
```typescript
// In src/components/layout/MainLayout.tsx
// Change this line to match package.json version:
<p className="text-sm text-gray-400">Sales Contact Form Version: 2.0.0</p>
```

**Automated Fix**:
```bash
bun run version-sync
```

### ❌ "installer.php version mismatch"

**Cause**: INSTALLER_VERSION constant is wrong

**Manual Fix**:
```php
// In installer.php
// Change this line to match package.json version:
define('INSTALLER_VERSION', '2.0.0');
```

**Automated Fix**:
```bash
bun run version-sync
```

## Emergency Procedures

### 🚨 Version System Broken

If the version system itself becomes corrupted:

```bash
# 1. Check what's wrong
bun run validate-versions 2>&1 | tee validation-error.log

# 2. Manually fix critical files
# Edit package.json to correct version
# Edit installer.php INSTALLER_VERSION
# Edit MainLayout.tsx footer version

# 3. Test validation
bun run validate-versions

# 4. If still broken, restore scripts from git
git checkout HEAD -- scripts/
git checkout HEAD -- .github/workflows/

# 5. Re-test
bun run validate-versions
```

### 🚨 CI/CD Completely Broken

If GitHub Actions are failing:

```bash
# 1. Disable failing workflows temporarily
# Comment out problematic steps in workflow files

# 2. Fix versions locally
bun run version-sync
bun run validate-versions

# 3. Test build locally
bun run build

# 4. Create minimal fix commit
git add .
git commit -m "EMERGENCY: Fix version consistency"
git push

# 5. Re-enable full workflows
# Uncomment disabled steps
```

### 🚨 Production Deployment Emergency

If production needs immediate deployment despite version issues:

```bash
# 1. Create emergency deployment package
mkdir emergency-deploy
cp -r out emergency-deploy/
cp installer.php emergency-deploy/
cp .htaccess emergency-deploy/
cd emergency-deploy && zip -r ../emergency-contact-form.zip .

# 2. Manual version fix in deployment package
# Edit installer.php in the zip to correct version
# Edit any HTML files with wrong versions

# 3. Deploy manually
# Upload emergency-contact-form.zip

# 4. Fix source code ASAP
bun run version-sync
bun run validate-versions
git add .
git commit -m "HOTFIX: Restore version consistency"
git push
```

## Best Practices

### ✅ DO

- Always run `bun run validate-versions` before committing
- Use automated scripts for version changes
- Test releases with `--dry-run` first
- Keep the version system updated
- Document any custom version references

### ❌ DON'T

- Edit version numbers manually across files
- Skip validation scripts
- Commit when validation fails
- Disable CI validation without good reason
- Create releases without running the automation

## Version System Maintenance

### Monthly Check

```bash
# 1. Validate entire system
bun run version:validate-all

# 2. Test all scripts
bun run version-sync:dry-run
bun run create-release:dry-run

# 3. Update documentation if needed
# Review this guide for accuracy
```

### After System Updates

```bash
# 1. Test version system still works
bun run validate-versions

# 2. Update scripts if needed
# Add new file patterns to validation
# Update sync script for new files

# 3. Test CI/CD workflows
# Trigger workflow manually to verify
```

---

## 🔐 System Integrity

This bulletproof system is designed to be **failure-resistant**:

- **Multiple validation layers** catch errors at different stages
- **Automatic rollback** prevents partial updates
- **Detailed logging** helps diagnose issues quickly
- **CI/CD integration** prevents broken deployments
- **Emergency procedures** handle edge cases

**Remember**: The system is only as strong as your commitment to following it. Always run validation scripts and never bypass safeguards without understanding the consequences.

---

**Last Updated**: August 2, 2025
**System Version**: 2.0.0 (Bulletproof)
**Maintained By**: GlowHost Contact Form Team

🤖 Generated with [Same](https://same.new)
Co-Authored-By: Same <noreply@same.new>
