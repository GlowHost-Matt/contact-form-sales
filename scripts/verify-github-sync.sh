#!/bin/bash

# GitHub Sync Verification Script
# Prevents AI hallucinations about git/GitHub status

echo "🔍 GITHUB SYNC VERIFICATION REPORT"
echo "=================================="
echo ""

# Check if we're in a git repository
echo "📁 Repository Status:"
if git rev-parse --git-dir > /dev/null 2>&1; then
    echo "✅ Git repository detected"
    REPO_EXISTS=true
else
    echo "❌ NOT a git repository"
    REPO_EXISTS=false
fi
echo ""

# If no repo, exit early
if [ "$REPO_EXISTS" = false ]; then
    echo "🚨 RESULT: No git repository found"
    echo "   Run 'git init' to initialize"
    exit 1
fi

# Check working directory status
echo "📋 Working Directory:"
if [ -z "$(git status --porcelain)" ]; then
    echo "✅ Working directory clean"
else
    echo "⚠️  Uncommitted changes detected:"
    git status --porcelain | head -5
    UNCOMMITTED_COUNT=$(git status --porcelain | wc -l)
    echo "   ($UNCOMMITTED_COUNT files modified)"
fi
echo ""

# Check for commits
echo "📝 Commit History:"
if git log --oneline -1 > /dev/null 2>&1; then
    LAST_COMMIT=$(git log -1 --pretty=format:"%h %s" 2>/dev/null)
    echo "✅ Commits exist"
    echo "   Latest: $LAST_COMMIT"
    COMMITS_EXIST=true
else
    echo "❌ No commits found"
    COMMITS_EXIST=false
fi
echo ""

# Check remote configuration
echo "🌐 Remote Configuration:"
if git remote -v | grep -q .; then
    echo "✅ Remote repositories configured:"
    git remote -v | sed 's/^/   /'
    REMOTE_EXISTS=true
else
    echo "❌ No remote repositories configured"
    REMOTE_EXISTS=false
fi
echo ""

# Check push status (only if commits and remote exist)
echo "📤 Push Status:"
if [ "$COMMITS_EXIST" = true ] && [ "$REMOTE_EXISTS" = true ]; then
    # Check if we can reach the remote
    if git ls-remote origin > /dev/null 2>&1; then
        echo "✅ Remote repository accessible"

        # Check for unpushed commits
        UNPUSHED=$(git log --branches --not --remotes --oneline 2>/dev/null | wc -l)
        if [ "$UNPUSHED" -eq 0 ]; then
            echo "✅ All commits pushed to remote"
        else
            echo "⚠️  $UNPUSHED unpushed commits detected"
            echo "   Recent unpushed commits:"
            git log --branches --not --remotes --oneline | head -3 | sed 's/^/   /'
        fi
    else
        echo "❌ Cannot access remote repository"
        echo "   (Check network connection or authentication)"
    fi
else
    echo "⏸️  Cannot check push status"
    if [ "$COMMITS_EXIST" = false ]; then
        echo "   Reason: No commits to push"
    fi
    if [ "$REMOTE_EXISTS" = false ]; then
        echo "   Reason: No remote configured"
    fi
fi
echo ""

# Generate final status
echo "🎯 FINAL STATUS SUMMARY:"
echo "========================"

if [ "$REPO_EXISTS" = false ]; then
    echo "❌ NOT CONNECTED: No git repository"
elif [ "$COMMITS_EXIST" = false ]; then
    echo "⚠️  NOT SYNCED: Repository exists but no commits"
elif [ "$REMOTE_EXISTS" = false ]; then
    echo "⚠️  NOT CONNECTED: Local commits exist but no GitHub remote"
elif [ "$UNPUSHED" -gt 0 ]; then
    echo "⚠️  PARTIALLY SYNCED: $UNPUSHED commits not pushed to GitHub"
else
    echo "✅ FULLY SYNCED: All commits pushed to GitHub"
fi

echo ""
echo "📅 Report generated: $(date)"
echo "🗂️  Current directory: $(pwd)"
