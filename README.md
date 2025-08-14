# Modular Installer v1 - Permanent Backup

**Milestone Date:** August 14, 2025
**Status:** Stable, Ready for Deployment

## 🚨 Insurance Policy: Your Permanent Backup

This branch (`modular-installer-v1`) contains a complete, working, and modular version of the PHP installer. It was created to secure our progress and provide a reliable recovery point.

If our session times out, the AI agent changes, or any other issue occurs, you can use the files in this branch to instantly restore the installer to this exact known-good state.

### How to Download This Backup

You can download a complete copy of these files at any time.

1.  **Go to Your GitHub Repository:**
    *   Navigate to this URL in your browser:
        **https://github.com/GlowHost-Matt/contact-form-sales**

2.  **Switch to the Backup Branch:**
    *   On the repository page, you will see a button that likely says "**`main`**". Click on this button.
    *   A dropdown menu will appear listing all branches. Select the new branch: **`modular-installer-v1`**.

3.  **Download the ZIP File:**
    *   After selecting the branch, the page will refresh. Now, look for a green button that says **"<> Code"**.
    *   Click the green "**<> Code**" button.
    *   In the dropdown menu that appears, click the last option: **"Download ZIP"**.

This will download a file named `contact-form-sales-modular-installer-v1.zip` to your computer. **This ZIP file is your insurance policy.** It contains the exact, working code and instructions we've agreed upon, independent of me or this chat session.

## 🏛️ System Architecture

This installer uses a clean, two-file modular system to separate logic from presentation.

### 1. `logic.php` (The "Brain")
*   **Purpose:** This file contains all the PHP code and business logic. It does not produce any HTML output itself.
*   **Responsibilities:**
    *   Performs "Pre-Flight Checks" to ensure the server environment is compatible (checks PHP version, required extensions).
    *   Dynamically and safely detects the system username from the server environment to create a database prefix (e.g., `contactglowhost_`).
    *   Holds all functions needed for the installation process.

### 2. `install.php` (The "Face")
*   **Purpose:** This is the user-facing file that you visit in your browser.
*   **Responsibilities:**
    *   Its very first action is to `require_once 'logic.php'`, which gives it access to all the logic and variables from the "brain."
    *   It contains all the HTML and CSS to create the user interface of the installer.
    *   It displays the results of the pre-flight checks.
    *   It displays the database configuration form and uses a simple `echo` to pre-fill the fields with the prefix detected by `logic.php`.

## 🚀 How to Deploy This Installer to Your Server

You can deploy this installer at any time by running a single command in your server's SSH terminal.

1.  **Log into your server** and navigate to your web root (`~/public_html`).
2.  **Run the command below.** This command will:
    *   Create a dated backup of your current `public_html` directory.
    *   Wipe the directory clean.
    *   Download the `logic.php` and `install.php` files directly from this GitHub branch.
    *   Set the correct permissions.

```bash
cd ~/public_html && backup_dir=~/backups/modular-installer-$(date +%F_%H-%M-%S) && mkdir -p "$backup_dir" && (shopt -s dotglob; mv * "$backup_dir"/ 2>/dev/null || true) && curl -L -o logic.php https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/modular-installer-v1/logic.php && curl -L -o install.php https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/modular-installer-v1/install.php && chmod 644 logic.php install.php && echo "Modular installer deployed successfully."
```

3.  **Begin Installation:**
    *   Visit `https://contact.glowhost.com/install.php` in your browser to start the installation process.