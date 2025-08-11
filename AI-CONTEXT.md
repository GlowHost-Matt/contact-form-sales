# 🚨 AI CONTEXT & PERMANENT RULES 🚨

## 🚨 CRITICAL SAFETY PROTOCOL - READ FIRST

### Q: ANALYSIS-ONLY MODE - MANDATORY COMPLIANCE
- ANY message starting with "q:" or "Q:" = ANALYSIS ONLY
- NEVER take actions without explicit approval after q: questions
- ALWAYS respond: "This is analysis-only per q: protocol"
- VIOLATION = IMMEDIATE HALT - Ask user to confirm before proceeding

**NUCLEAR RULE: If unsure about q: compliance, ASK before acting**

### MANDATORY URL TESTING PROTOCOL
- BEFORE directing user to any URL, ALWAYS test it first with web_scrape
- NEVER assume GitHub file = working server URL
- If URL returns 404/500, identify the real issue and provide working solution
- Include URL verification as standard QA step in all recommendations

## 🚨 GITHUB OPERATIONS - MANDATORY RULE

### **NEVER Use task_agent for GitHub Operations**
- **ALWAYS use direct `gh` CLI commands for GitHub operations**
- **NEVER use `task_agent` with GitHub integration**
- **Reason:** Task agent reports false success while failing silently, wasting hours of debugging time

### **Working GitHub Patterns**
```bash
# Create/update file
gh api repos/owner/repo/contents/file.ext \
  --method PUT \
  --field message="commit message" \
  --field content="$(base64 -i file.ext)" \
  --field branch="main"

# Delete file
gh api repos/owner/repo/contents/file.ext \
  --method DELETE \
  --field message="delete file" \
  --field sha="$(gh api repos/owner/repo/contents/file.ext --jq .sha)" \
  --field branch="main"
```

### **Evidence of Failure**
Multiple documented cases where task_agent claimed "Successfully committed" but GitHub showed no changes. Direct CLI commands work immediately and reliably.

**WORKFLOW REQUIREMENT:** Every AI edit to installer files MUST be committed via `gh` CLI before providing wget URLs to user.

## 🚨 MANDATORY FIRST CHECKS - BEFORE ANY ACTION

### **Pre-Action Checklist** (Check EVERY time before responding):
1. **Q: Protocol Check:** Does message start with "q:" or "Q:"? → Analysis only, no actions
2. **GitHub Rule Check:** Need GitHub operation? → Use `gh` CLI, NEVER task_agent
3. **URL Testing:** Directing to URL? → Test with web_scrape first
4. **Installer Updates:** Changed installer? → Commit via `gh` CLI before wget URLs

### **Violation Response Protocol**
If ANY rule violated:
1. IMMEDIATELY acknowledge violation
2. Explain what should have happened
3. ASK for permission before proceeding
4. Document the failure for prevention

### **Critical Reminder**
- When in doubt, ASK first
- User should never need to add safety reminders to prompts
- These rules exist to prevent wasted time and money

---

**Project**: GlowHost Enterprise Contact Form System
**Version**: 3.0.0
**Status**: ACTIVE DEVELOPMENT
**Primary Goal**: Create a professional, database-driven contact form system with a one-click installer.

---

## 🎯 Core Objective: Single-File Installer

The primary deliverable is a **single `installer.php` file** (<100KB).

### Installer Requirements:
1. **Self-Contained**: No other files needed to start.
2. **Auto-Download**: Fetches the latest release from a specified GitHub URL.
3. **Pre-flight Checks**:
    - `PHP Version >= 7.4`
    - `ZipArchive` extension loaded.
    - `allow_url_fopen` or `cURL` available.
    - Directory is writable.
4. **Error Handling**: Provides clear, user-friendly HTML error messages if checks fail.
5. **Extraction**: Unzips the downloaded package.
6. **Redirection**: Automatically forwards the user to the `/install/` wizard.
7. **Cleanup**: Removes temporary files after extraction.

---

## 🔧 Deployment & Operational Pipeline

### Command Safety Rule: ONE Command per Block
- Provide **one command per copy block**. Avoid chaining multiple commands with `&&`.
- If a command is longer than ~80 characters, split it across multiple lines **inside the fenced block** using a Bash back-slash continuation (`\`) so the copy button remains unobstructed, e.g.:
    ```bash
    wget \
      https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/installer.php \
      -O installer.php
    ```
- **Never** use placeholders like `[your_domain]` in commands. Use the actual domain, e.g., `contact.glowhost.com`.

### One-Command Installer Restore
These are the permanent, single-command methods to deploy the installer.

**1. Minimalist Installer (Downloads full wizard):**
```bash
wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/installer.php -O installer.php
```

**2. Full Progressive Installer (All-in-one):**
```bash
wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/installer.php -O installer.php
```

---

## 🗂️ Project Structure

- `installer.php`: Single-file, self-extracting installer.
- `installer.php`: The full, multi-step progressive installation wizard.
- `/install/`: Directory containing the wizard's steps and assets.
- `/admin/`: Backend admin interface (generated after installation).
- `config.php`: System configuration file (generated after installation).

---

## 🏆 Definition of Done

The project is complete when a non-technical user can:
1. Upload a single `installer.php` file to their web server.
2. Navigate to `https://their-domain.com/installer.php`.
3. Successfully complete the installation wizard without errors.
4. Log into a functional admin panel to view form submissions.

This document is the **single source of truth** for all AI agents to ensure continuity and prevent regressions.
