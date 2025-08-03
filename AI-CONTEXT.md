## 🛠️ **PERMANENT SINGLE-FILE INSTALLER PIPELINE**

### **Authoritative Source**
- The file **`install.php`** in the root of the `main` branch of the GitHub repo **GlowHost-Matt/contact-form-sales** is the ONLY authoritative copy.
- All production servers fetch it with:
  wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php

### **Update Rules**
1. **Every AI edit** to the installer **MUST** be committed & pushed to `main` via `task_agent` _before_ telling the user to `wget`.
2. **Never require copy-paste** – always provide a raw GitHub URL.
3. The installer **downloads & extracts** the `/install` wizard automatically; no manual unzip commands should be necessary for end-users.
4. Keep the installer **ASCII-safe** (no smart quotes) to avoid parse errors on shared hosts.

### **Server Workflow (cPanel example)**
```bash
ssh contactglowhost@juliet
cd ~/public_html
rm -f install.php && wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/install.php
chmod 644 install.php
```
