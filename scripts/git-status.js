#!/usr/bin/env node

/**
 * Git Status Verification (Node.js)
 * Prevents AI hallucinations about git/GitHub sync status
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// ANSI color codes for terminal output
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

function runCommand(command, silent = false) {
  try {
    const result = execSync(command, {
      encoding: 'utf8',
      stdio: silent ? 'pipe' : 'inherit'
    });
    return { success: true, output: result.trim() };
  } catch (error) {
    return { success: false, error: error.message };
  }
}

function checkGitRepository() {
  log('\n🔍 GITHUB SYNC VERIFICATION', 'bold');
  log('==================================');

  // Check if .git directory exists
  const gitCheck = runCommand('git rev-parse --git-dir', true);
  if (!gitCheck.success) {
    log('\n❌ NOT a git repository', 'red');
    log('   Run "git init" to initialize', 'yellow');
    return { isRepo: false };
  }

  log('✅ Git repository detected', 'green');
  return { isRepo: true };
}

function checkWorkingDirectory() {
  log('\n📋 Working Directory Status:', 'blue');

  const status = runCommand('git status --porcelain', true);
  if (!status.success) {
    log('❌ Cannot check working directory status', 'red');
    return { clean: false };
  }

  if (status.output === '') {
    log('✅ Working directory clean', 'green');
    return { clean: true };
  } else {
    const lines = status.output.split('\n');
    log(`⚠️  ${lines.length} uncommitted changes detected:`, 'yellow');
    lines.slice(0, 5).forEach(line => {
      log(`   ${line}`, 'yellow');
    });
    if (lines.length > 5) {
      log(`   ... and ${lines.length - 5} more`, 'yellow');
    }
    return { clean: false, changes: lines.length };
  }
}

function checkCommits() {
  log('\n📝 Commit History:', 'blue');

  const logCheck = runCommand('git log --oneline -1', true);
  if (!logCheck.success) {
    log('❌ No commits found', 'red');
    return { hasCommits: false };
  }

  log('✅ Commits exist', 'green');
  log(`   Latest: ${logCheck.output}`, 'reset');
  return { hasCommits: true, latest: logCheck.output };
}

function checkRemote() {
  log('\n🌐 Remote Configuration:', 'blue');

  const remoteCheck = runCommand('git remote -v', true);
  if (!remoteCheck.success || remoteCheck.output === '') {
    log('❌ No remote repositories configured', 'red');
    return { hasRemote: false };
  }

  log('✅ Remote repositories configured:', 'green');
  remoteCheck.output.split('\n').forEach(line => {
    log(`   ${line}`, 'reset');
  });
  return { hasRemote: true, remotes: remoteCheck.output };
}

function checkPushStatus(hasCommits, hasRemote) {
  log('\n📤 Push Status:', 'blue');

  if (!hasCommits || !hasRemote) {
    log('⏸️  Cannot check push status', 'yellow');
    if (!hasCommits) log('   Reason: No commits to push', 'yellow');
    if (!hasRemote) log('   Reason: No remote configured', 'yellow');
    return { canCheck: false };
  }

  // Check if remote is accessible
  const remoteCheck = runCommand('git ls-remote origin', true);
  if (!remoteCheck.success) {
    log('❌ Cannot access remote repository', 'red');
    log('   (Check network connection or authentication)', 'yellow');
    return { canCheck: false, accessible: false };
  }

  log('✅ Remote repository accessible', 'green');

  // Check for unpushed commits
  const unpushedCheck = runCommand('git log --branches --not --remotes --oneline', true);
  if (!unpushedCheck.success) {
    log('⚠️  Cannot determine push status', 'yellow');
    return { canCheck: false };
  }

  const unpushedCommits = unpushedCheck.output ? unpushedCheck.output.split('\n').length : 0;

  if (unpushedCommits === 0) {
    log('✅ All commits pushed to remote', 'green');
    return { canCheck: true, unpushed: 0 };
  } else {
    log(`⚠️  ${unpushedCommits} unpushed commits detected`, 'yellow');
    log('   Recent unpushed commits:', 'yellow');
    unpushedCheck.output.split('\n').slice(0, 3).forEach(line => {
      if (line.trim()) log(`   ${line}`, 'yellow');
    });
    return { canCheck: true, unpushed: unpushedCommits };
  }
}

function generateFinalStatus(results) {
  log('\n🎯 FINAL STATUS SUMMARY:', 'bold');
  log('========================');

  if (!results.repo.isRepo) {
    log('❌ NOT CONNECTED: No git repository', 'red');
    return 'no-repo';
  }

  if (!results.commits.hasCommits) {
    log('⚠️  NOT SYNCED: Repository exists but no commits', 'yellow');
    return 'no-commits';
  }

  if (!results.remote.hasRemote) {
    log('⚠️  NOT CONNECTED: Local commits exist but no GitHub remote', 'yellow');
    return 'no-remote';
  }

  if (!results.push.canCheck) {
    log('⚠️  UNKNOWN: Cannot determine sync status', 'yellow');
    return 'unknown';
  }

  if (results.push.unpushed > 0) {
    log(`⚠️  PARTIALLY SYNCED: ${results.push.unpushed} commits not pushed to GitHub`, 'yellow');
    return 'partial-sync';
  }

  log('✅ FULLY SYNCED: All commits pushed to GitHub', 'green');
  return 'synced';
}

// Main execution
function main() {
  const results = {
    repo: checkGitRepository(),
    workingDir: null,
    commits: null,
    remote: null,
    push: null
  };

  // Only continue if we have a git repository
  if (!results.repo.isRepo) {
    process.exit(1);
  }

  results.workingDir = checkWorkingDirectory();
  results.commits = checkCommits();
  results.remote = checkRemote();
  results.push = checkPushStatus(results.commits.hasCommits, results.remote.hasRemote);

  const finalStatus = generateFinalStatus(results);

  log(`\n📅 Report generated: ${new Date().toISOString()}`, 'reset');
  log(`🗂️  Current directory: ${process.cwd()}`, 'reset');

  // Return appropriate exit code
  if (finalStatus === 'synced') {
    process.exit(0);
  } else if (finalStatus === 'no-repo') {
    process.exit(1);
  } else {
    process.exit(2); // Partially synced or unknown
  }
}

// Run if called directly
if (require.main === module) {
  main();
}

module.exports = {
  checkGitRepository,
  checkWorkingDirectory,
  checkCommits,
  checkRemote,
  checkPushStatus,
  generateFinalStatus
};
