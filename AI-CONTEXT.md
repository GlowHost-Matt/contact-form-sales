# 🚨 AI CONTEXT & PERMANENT RULES 🚨

## 🚨 CRITICAL SAFETY PROTOCOL - READ FIRST

### Q: ANALYSIS-ONLY MODE - MANDATORY COMPLIANCE
- ANY message starting with "q:" or "Q:" = ANALYSIS ONLY
- NEVER take actions without explicit approval after q: questions
- ALWAYS respond: "This is analysis-only per q: protocol"
- VIOLATION = IMMEDIATE HALT - Ask user to confirm before proceeding

**NUCLEAR RULE: If unsure about q: compliance, ASK before acting**

---

**Project**: GlowHost Enterprise Contact Form System
**Version**: 3.0.0
**Status**: ACTIVE DEVELOPMENT
**Primary Goal**: Create a professional, database-driven contact form system with a one-click installer.

---

## 🎯 Core Objective: Single-File Installer

The primary deliverable is a **single `install.php` file** (<100KB).

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
      https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php \
      -O install.php
    ```
- **Never** use placeholders like `[your_domain]` in commands. Use the actual domain, e.g., `contact.glowhost.com`.

### One-Command Installer Restore
These are the permanent, single-command methods to deploy the installer.

**1. Minimalist Installer (Downloads full wizard):**
```bash
wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php -O install.php
```

**2. Full Progressive Installer (All-in-one):**
```bash
wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/installer.php -O installer.php
```

---

## 🗂️ Project Structure

- `install.php`: Single-file, self-extracting installer.
- `installer.php`: The full, multi-step progressive installation wizard.
- `/install/`: Directory containing the wizard's steps and assets.
- `/admin/`: Backend admin interface (generated after installation).
- `config.php`: System configuration file (generated after installation).

---

## 🏆 Definition of Done

The project is complete when a non-technical user can:
1. Upload a single `install.php` file to their web server.
2. Navigate to `https://their-domain.com/install.php`.
3. Successfully complete the installation wizard without errors.
4. Log into a functional admin panel to view form submissions.

This document is the **single source of truth** for all AI agents to ensure continuity and prevent regressions.
