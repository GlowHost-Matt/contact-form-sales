# Git Branch Standards - PERMANENT REFERENCE

## 🎯 **CRITICAL: Always Use `main` Branch**

### **Standard Operating Procedure:**
- **✅ USE: `main` branch** - Modern, active, production-ready
- **❌ NEVER USE: `master` branch** - Deprecated, outdated, legacy code

### **Repository: GlowHost-Matt/contact-form-sales**
- **Default Branch**: `main`
- **Production Branch**: `main`
- **Development Branch**: `main`
- **Deployment Source**: `main`

## 📋 **Commands for AI Assistants:**

### **Always use these commands:**
```bash
# Clone repository
git clone https://github.com/GlowHost-Matt/contact-form-sales.git
git checkout main

# Push to correct branch
git push origin main

# Create branches from main
git checkout main
git checkout -b feature/new-feature
```

### **MCP GitHub Operations:**
- Always specify `main` branch in GitHub API calls
- Never reference `master` branch in commits or pushes
- All file updates go to `main` branch only

## 🚫 **Branch Status:**
- **`main`**: ✅ **ONLY BRANCH** - Contains v1.2 installer, latest features, production-ready
- **`master`**: 🗑️ **PERMANENTLY DELETED** - Eliminated to prevent confusion (July 31, 2025)

## 📅 **Decision Date:** July 31, 2025
**Reason:** Eliminates time-consuming branch confusion, follows modern Git standards

## ⚠️ **For Future AI Interactions:**
**READ THIS FIRST before any Git operations:**
1. Always check this file before Git commands
2. Use `main` branch exclusively
3. Never assume `master` is the default
4. When in doubt, verify current branch with GitHub API

## 🔄 **Update Instructions:**
If this standard changes, update this file AND inform the user immediately.

---
**This document prevents repeated branch confusion and saves development time.**


## 🗑️ **Master Branch Elimination (July 31, 2025):**
**PERMANENT ARCHITECTURAL DECISION:** The deprecated `master` branch has been completely removed from the GitHub repository to eliminate confusion. The repository now uses ONLY the `main` branch, ensuring:
- ✅ Single source of truth
- ✅ No master/main confusion possible  
- ✅ Consistent AI interactions
- ✅ Modern Git conventions
- ✅ Future-proof architecture
