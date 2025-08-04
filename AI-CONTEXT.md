## 🛠️ **PERMANENT SINGLE-FILE INSTALLER PIPELINE**
### **Authoritative Source**
- The file **`start.php`** in the root of the `main` branch of the GitHub repo **GlowHost-Matt/contact-form-sales** is the ONLY authoritative copy.
- All production servers fetch it with:
  ```bash
  wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/start.php
  ```
### **Update Rules**
1. **Every AI edit** to the installer **MUST** be committed & pushed to `main` via direct GitHub CLI _before_ telling the user to `wget`.
2. **Never require copy-paste** – always provide a raw GitHub URL.
3. The installer **downloads & extracts** the `installer.php` wizard automatically; no manual unzip commands should be necessary for end-users.
4. Keep the installer **ASCII-safe** (no smart quotes) to avoid parse errors on shared hosts.

### **Server Workflow (cPanel example)**
```bash
ssh contactglowhost@juliet
cd ~/public_html
rm -f start.php && wget https://raw.githubusercontent.com/GlowHost-Matt/contact-form-sales/main/start.php
chmod 644 start.php
```

---

## 🚨 **GITHUB OPERATIONS - MANDATORY RULE**

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

---

## 💬 **USER COMMUNICATION SHORTCUTS**

### **Question Shortcut**
When user types **`q:`** or **`Q:`** followed by a question, treat it as:
> "Just a question, no commitment. Give me the pros and cons without taking any action."

**Examples:**
- `q: should we rename the file?` 
- `Q: what if we used a different approach?`

**Response format:**
1. Acknowledge it's analysis-only
2. Provide clear pros/cons 
3. Give recommendation
4. Ask for explicit approval before any action

This prevents accidental implementations and saves time on exploratory discussions.
