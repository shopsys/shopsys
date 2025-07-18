---
description: "Universal tmux SSH automation framework for any server"
---

# Tmux SSH Automation Framework

This document provides a comprehensive, server-agnostic framework for tmux-based SSH automation. Use this framework to efficiently execute commands and capture results on ANY SSH server through automated tmux workflows.

## Overview

Traditional server debugging requires manually executing commands over SSH and copying results. This framework provides proven automation patterns that work with any SSH server:

- **Automated command execution** in remote SSH sessions
- **Clean result capture** without manual copying  
- **Consistent timing patterns** for command completion
- **Reusable automation building blocks** for any server
- **Security-conscious manual authentication**
- **Command safety framework** with clear previews and risk assessment

## Core Principles

### 1. Server Agnostic Design
- Works with ANY SSH server (cloud, on-premise, containers, VMs)
- No hardcoded hostnames, IPs, or credentials
- Generic patterns adaptable to any environment

### 2. Manual Security Boundary  
- User controls all authentication and SSH connections
- User manages tmux panes and sessions
- Claude automates only command execution after manual setup

### 3. Universal Automation Patterns
- Proven marker-based output capture
- Reliable timing and synchronization
- Flexible pane targeting system
- Robust error handling

### 4. Command Safety Framework
- **Safe Commands** (✅): Auto-execute read-only operations
- **Performance Heavy** (⚡): Auto-execute with performance warnings
- **Mutating Commands** (⚠️): Require explicit user confirmation
- Clear command previews before execution

## Prerequisites

### CRITICAL: Claude Code Must Be Spawned in tmux

**STOP - READ THIS FIRST**: This automation framework ONLY works when Claude Code is already running inside a tmux session.

#### tmux Session Verification

**Before proceeding, verify you're in tmux:**

```bash
# Check if you're in tmux session
echo $TMUX
# Expected output: /tmp/tmux-{uid}/default,{session-id},0
# If empty: You're NOT in tmux
```

#### If NOT in tmux Session

**❌ CANNOT PROCEED**: If `echo $TMUX` returns empty, this framework will NOT work.

**Solution**: 
1. **Exit this Claude Code instance completely**
2. **Start tmux session first**: `tmux new-session -s automation` 
3. **Launch Claude Code INSIDE the tmux session**
4. **Return to this document**

**Why this is required:**
- Claude Code cannot create tmux sessions (it's a CLI tool running inside your environment)  
- All tmux automation requires Claude Code to be spawned within an existing tmux context
- Manual tmux session management must be done by YOU before starting Claude Code

#### Required Access
- **SSH access to target servers** (any authentication method)
- **Active tmux session** with Claude Code running inside it
- **Basic tmux knowledge** for pane management

### Supported Server Types
This framework works with ANY SSH-accessible server:
- ✅ **Cloud instances** (AWS, GCP, Azure, etc.)
- ✅ **On-premise servers** (Linux, Unix variants)
- ✅ **Container hosts** (Docker hosts, Kubernetes nodes)
- ✅ **Development environments** (staging, testing servers)
- ✅ **Production systems** (with appropriate caution)
- ✅ **Jump boxes and bastion hosts**

## Universal Manual Setup Workflow

This section establishes the manual setup phase that must be completed before automation can begin. This workflow works with ANY SSH server.

### Step 1: Verify tmux Environment

**YOU DO THIS**: Confirm you're in tmux and ready for automation

```bash
# Verify tmux session
echo $TMUX
# Should show: /tmp/tmux-{uid}/default,{session-id},0

# Optional: Check current panes  
tmux list-panes
# Shows current pane layout and numbering
```

### Step 2: Create New Pane

**YOU DO THIS**: Create a dedicated pane for your SSH session

```bash
# Create horizontal split pane
tmux split-window -h

# Alternative: Create vertical split
tmux split-window -v

# Alternative: Create in specific window
tmux new-window -n "server-name"
```

### Step 3: SSH to Target Server

**YOU DO THIS**: Connect to your target server using ANY SSH method

**Examples (replace with your actual server details):**
```bash
# Standard SSH
ssh user@hostname

# SSH with custom port
ssh -p 2222 user@hostname

# SSH with key file
ssh -i ~/.ssh/custom-key user@hostname

# SSH through jump host
ssh -J jumphost@jump.example.com user@target.example.com

# SSH with custom config
ssh server-alias  # (defined in ~/.ssh/config)
```

### Step 4: Confirm Connection

**YOU DO THIS**: Verify successful login and identify pane

1. **Verify server prompt**: Ensure you see the target server's shell prompt
2. **Test basic command**: Run a simple command like `pwd` or `hostname`
3. **Identify pane number**: Check which tmux pane contains your SSH session

```bash
# Check pane numbers if needed
tmux list-panes
# Example output:
# 0: [80x24] [history 1/1000, 0 bytes] %0 (active)
# 1: [80x24] [history 0/1000, 0 bytes] %1
```

### Step 5: Automation Handoff

**TELL CLAUDE**: Confirm your setup with this format:

```
"I'm connected to [server-description] in pane [number]"
```

**Examples:**
- "I'm connected to production-web-01 in pane 1"
- "I'm connected to staging database server in pane 2"  
- "I'm connected to AWS EC2 instance in pane 1"

### Critical Success Criteria

✅ **tmux verified**: `echo $TMUX` shows session path  
✅ **SSH successful**: You see target server prompt  
✅ **Pane identified**: You know which pane number contains SSH  
✅ **Claude informed**: You've told Claude the server and pane details

**Once these criteria are met, automation can begin safely.**

## Core Automation Framework

This section provides the fundamental automation building blocks that work with any SSH server and tmux setup.

### Automation Boundary Definition

**What YOU Control:**
- 🔐 **Authentication**: All SSH login and credential management
- 🏗️ **Session Management**: tmux session creation and pane management
- 🎯 **Server Selection**: Which servers to connect to and when
- ✅ **Verification**: Confirming successful connections and setup

**What CLAUDE Automates:**
- ⚡ **Command Execution**: Sending commands to your SSH pane automatically
- 📋 **Output Capture**: Capturing and filtering command results cleanly
- ⏱️ **Timing Management**: Handling command completion timing
- 🧹 **Result Formatting**: Presenting clean output without manual copying

### Universal Command Execution Pattern

The core pattern that works with any server and any command:

```bash
# Universal automation template (Claude executes this)
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t {pane} "{your-command}" Enter  
sleep {timeout}
tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

**Placeholder explanations:**
- `{pane}`: The tmux pane number containing your SSH session (e.g., 1, 2, 3)
- `{your-command}`: Any command to execute on the remote server  
- `{timeout}`: Wait time for command completion (3-10 seconds typical)

### Why This Pattern Works

#### 1. Marker-Based Isolation
- **START marker**: Clearly identifies where command output begins
- **END marker**: Clearly identifies where command output ends  
- **Unique timestamps**: Prevents conflicts with multiple commands
- **Immune to scrolling**: Works regardless of terminal buffer changes

#### 2. Reliable Output Capture
- **Extended buffer**: `-S -5000` captures large outputs without context overload
- **Precise filtering**: `sed` extracts only content between markers
- **Clean results**: Removes marker lines from final output

#### 3. Flexible Timing
- **Pre-command delay**: 0.5s ensures marker appears before command
- **Command timeout**: Adjustable based on expected execution time
- **Post-command delay**: 0.5s ensures end marker appears after command

## Command Safety Framework

### Command Classification System

Before executing any command, Claude will present a clear preview with safety classification:

#### ✅ SAFE COMMANDS (Auto-Execute)
Read-only operations that don't modify the system:
- **File operations**: `ls`, `cat`, `head`, `tail`, `find`, `grep`
- **System info**: `ps`, `df`, `free`, `uptime`, `hostname`
- **Network info**: `netstat`, `ss`, `ping`, `curl -I`
- **Version checks**: `docker --version`, `git --version`
- **Status checks**: `git status`, `docker ps`, `systemctl status`

#### ⚡ PERFORMANCE HEAVY (Auto-Execute with Warning)
Resource-intensive operations that are safe but may take time:
- **Container operations**: `docker compose up`, `docker build`
- **Package management**: `npm install`, `composer install`
- **Database operations**: `mysqldump`, `pg_dump`
- **File operations**: `find /` (large searches), `rsync`
- **System operations**: `make build`, `phing build`

#### ⚠️ MUTATING COMMANDS (Require Confirmation)
System-changing operations that require explicit user approval:
- **File modifications**: `rm`, `mv`, `cp`, `chmod`, `chown`
- **System changes**: `sudo`, `systemctl start/stop/restart`
- **Package changes**: `apt install`, `yum install`, `npm uninstall`
- **Database changes**: `mysql`, `psql` (with modification queries)
- **Git operations**: `git commit`, `git push`, `git reset`
- **Container management**: `docker compose down`, `docker rm`

### Command Preview Format

**Standard Format (80-character width):**
```
✅ SAFE COMMAND
┌─ Command Preview ──────────────────────────────────────────────────────────────┐
│ docker exec github-runner-redis-1 redis-cli info keyspace                    │
└────────────────────────────────────────────────────────────────────────────────┘
```

**Performance Heavy Commands:**
```
⚡ PERFORMANCE HEAVY
┌─ Command Preview ──────────────────────────────────────────────────────────────┐
│ docker compose up -d                                                          │
└────────────────────────────────────────────────────────────────────────────────┘
```

**Mutating Commands (with confirmation):**
```
⚠️ MUTATING - USER CONFIRMATION REQUIRED
┌─ Command Preview ──────────────────────────────────────────────────────────────┐
│ sudo systemctl restart nginx                                                  │
└────────────────────────────────────────────────────────────────────────────────┘
⚠️ This command will modify the system. Proceed? (y/n)
```

**Multi-line Commands:**
```
✅ SAFE COMMAND
┌─ Command Preview ──────────────────────────────────────────────────────────────┐
│ cd ~/project &&                                                               │
│ git status &&                                                                 │
│ git branch -v                                                                 │
└────────────────────────────────────────────────────────────────────────────────┘
```

### Execution Workflow

1. **Safe & Performance Heavy**: Claude shows preview → automatically executes
2. **Mutating Commands**: Claude shows preview → asks for confirmation → waits for user response → executes only if approved

### Timing Guidelines by Command Type

**Quick commands** (ls, pwd, hostname): `sleep 2`
```bash
sleep 2  # 2 seconds for basic file operations
```

**System queries** (ps, netstat, df): `sleep 3`  
```bash
sleep 3  # 3 seconds for system information
```

**Database operations** (SQL queries, dumps): `sleep 5`
```bash
sleep 5  # 5 seconds for database operations
```

**Search/grep operations** (find, grep -r): `sleep 4`
```bash
sleep 4  # 4 seconds for filesystem searches
```

**Network operations** (curl, wget, ping): `sleep 6`
```bash  
sleep 6  # 6 seconds for network requests
```

**Long operations** (large file operations, complex scripts): `sleep 10`
```bash
sleep 10  # 10 seconds for complex operations
```

### Pane Targeting System

**Dynamic pane targeting** - works with any pane configuration:

```bash
# Check current pane layout
tmux list-panes

# Target specific pane by number
tmux send-keys -t 1 "command" Enter    # Target pane 1
tmux send-keys -t 2 "command" Enter    # Target pane 2  

# Target by pane ID (more precise)
tmux send-keys -t %1 "command" Enter   # Target pane ID %1
```

**Multiple SSH sessions** - manage multiple servers simultaneously:
```bash
# Server 1 in pane 1
tmux send-keys -t 1 "hostname" Enter

# Server 2 in pane 2  
tmux send-keys -t 2 "hostname" Enter

# Different commands, same timing pattern
```

## Universal Command Templates

Ready-to-use automation templates for common server administration tasks. Replace `{pane}` with your SSH pane number.

### Basic System Exploration

#### Server Information Discovery
```bash
# Get hostname and basic info
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "hostname && uname -a" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check current directory and permissions
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "pwd && ls -la" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check user and environment
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "whoami && id && env | head -20" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### File System Navigation
```bash
# Navigate to common directories
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "cd /var/log && pwd && ls -la" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check disk usage
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "df -h" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Find large files/directories
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "du -sh * | sort -hr | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Process and Service Management

#### Process Monitoring
```bash
# Check running processes
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ps aux | head -20" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check system load and memory
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "top -bn1 | head -20" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check network connections
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "netstat -tuln | head -20" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Service Status Checking
```bash
# Check systemd services (Linux)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "systemctl list-units --type=service --state=running | head -15" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check specific service status
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "systemctl status nginx" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Container and Virtualization

#### Docker Container Management
```bash
# List running containers
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "docker ps" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Get container logs (replace container-name)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "docker logs --tail 20 container-name" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Execute command in container
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "docker exec container-name command" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Log File Examination

#### Common Log Locations
```bash
# Check recent system logs
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "tail -20 /var/log/syslog" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check application logs (adjust path)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "tail -20 /var/log/application.log" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Search for errors in logs
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "grep -i error /var/log/syslog | tail -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Network Debugging

#### Connectivity Testing
```bash
# Test network connectivity
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ping -c 4 google.com" Enter; sleep 6; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Test specific service connectivity
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "curl -I http://localhost:80" Enter; sleep 6; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check DNS resolution
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "nslookup google.com" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Template Customization Guide

**To adapt any template:**

1. **Replace `{pane}`** with your actual pane number (1, 2, 3, etc.)
2. **Adjust commands** for your specific server/application  
3. **Modify timeouts** based on expected execution time
4. **Change paths** to match your server's file structure

**Example customization:**
```bash
# Original template
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ls -la" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Customized for pane 2, specific directory, longer timeout
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 2 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 2 "ls -la /var/www/html" Enter; sleep 5; tmux send-keys -t 2 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 2 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

## Universal Environment Discovery Framework

This section provides systematic approaches to discover and understand any server environment. Use these patterns to quickly assess new servers.

### Basic Environment Assessment

#### System Information Discovery
```bash
# Get comprehensive system information
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "uname -a && cat /etc/os-release" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check hardware resources
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "free -h && nproc && lscpu | grep 'Model name'" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check installed packages/software (Debian/Ubuntu)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "dpkg -l | grep -E 'nginx|apache|mysql|postgres|redis|docker' | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Service and Application Discovery

#### Web Services Detection
```bash
# Check for web servers and their status
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ps aux | grep -E 'nginx|apache|httpd' | grep -v grep" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Find web root directories  
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "find / -type d -name 'www' -o -name 'html' -o -name 'public_html' 2>/dev/null | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check listening ports for web services
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "netstat -tuln | grep -E ':80|:443|:8080|:3000|:8000'" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Database Services Detection
```bash
# Check for database processes
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ps aux | grep -E 'mysql|postgres|mongo|redis' | grep -v grep" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check database listening ports
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "netstat -tuln | grep -E ':3306|:5432|:27017|:6379'" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Look for database configuration files
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "find /etc -name '*mysql*' -o -name '*postgres*' -o -name '*redis*' 2>/dev/null | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Container and Orchestration Discovery

#### Docker Environment Assessment
```bash
# Check if Docker is installed and running
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "docker --version && docker info 2>/dev/null | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# List all containers (running and stopped)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "docker ps -a" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check for docker-compose files
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "find / -name 'docker-compose.yml' -o -name 'docker-compose.yaml' 2>/dev/null | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Kubernetes/Container Orchestration
```bash
# Check for Kubernetes tools
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "which kubectl && kubectl version 2>/dev/null || echo 'kubectl not found'" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check for container runtime processes
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ps aux | grep -E 'kubelet|containerd|crio' | grep -v grep" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Configuration and Environment Variables

#### Environment Variable Scanning
```bash
# Check for application-specific environment variables
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "env | grep -E 'DATABASE|REDIS|ELASTIC|APP_|NODE_|PYTHON_' | head -20" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check for cloud provider metadata (if applicable)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "curl -s -m 3 http://169.254.169.254/latest/meta-data/instance-id || echo 'Not AWS EC2'" Enter; sleep 5; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Look for common configuration directories
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ls -la /etc/ | grep -E 'nginx|apache|mysql|redis|php' | head -10" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Network and Connectivity Assessment

#### Network Interface Discovery
```bash
# Check network interfaces and IP addresses
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ip addr show || ifconfig" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check routing table
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ip route show || route -n" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Test external connectivity
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ping -c 2 8.8.8.8 && curl -s -m 5 http://httpbin.org/ip" Enter; sleep 8; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Application Stack Discovery

#### Programming Language Environments
```bash
# Check installed programming language runtimes
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "python3 --version 2>/dev/null && node --version 2>/dev/null && php --version 2>/dev/null | head -1" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Look for application directories
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "find /var /opt /home -maxdepth 2 -name 'package.json' -o -name 'composer.json' -o -name 'requirements.txt' 2>/dev/null | head -10" Enter; sleep 4; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check for application processes
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t {pane} "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t {pane} "ps aux | grep -E 'node|python|php-fpm|java|ruby' | grep -v grep | head -10" Enter; sleep 3; tmux send-keys -t {pane} "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t {pane} -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Discovery Workflow Template

**Complete environment assessment in stages:**

1. **Initial Assessment** - System info, hardware, OS
2. **Service Discovery** - Web servers, databases, containers  
3. **Network Analysis** - Interfaces, connectivity, ports
4. **Application Stack** - Runtimes, frameworks, processes
5. **Configuration Review** - Environment variables, config files

**Customize discovery based on findings:**
- Found Docker? → Deep dive into container architecture
- Found web server? → Explore web roots and virtual hosts  
- Found database? → Check connection settings and data paths
- Found specific framework? → Look for framework-specific patterns

## Best Practices and Guidelines

### Security Considerations

#### Authentication Security
- **Never automate credentials**: All SSH authentication must be manual
- **Individual accounts**: Use personal accounts, never shared credentials
- **Key-based authentication**: Prefer SSH keys over passwords where possible
- **Session isolation**: Keep SSH sessions in separate tmux panes for clarity

#### Command Execution Safety
- **Read-only first**: Start with read-only commands for exploration
- **Verify before destructive operations**: Never automate commands that modify data
- **Production caution**: Use extra care and verification on production systems
- **Audit trail**: Document all automated actions for security auditing

#### Network Security
- **Jump host compliance**: Follow organizational jump host/bastion requirements
- **VPN requirements**: Ensure proper VPN connections for restricted networks
- **Firewall awareness**: Understand network access restrictions

### Performance Optimization

#### Timing Optimization
- **Start conservative**: Begin with longer timeouts, then optimize down
- **Server-specific tuning**: Adjust timing based on server performance characteristics
- **Network latency consideration**: Account for network delays in remote locations
- **Load-aware timing**: Increase timeouts during high system load

#### Resource Management
- **Buffer size awareness**: `-S -5000` captures large outputs efficiently
- **Context efficiency**: Only filtered content reaches Claude (not raw buffer)
- **Memory usage**: Large outputs are processed locally, not stored in context
- **Parallel execution**: Multiple panes allow concurrent server management

#### Output Management
- **Marker reliability**: Unique timestamps prevent conflicts across commands
- **Clean formatting**: sed filtering removes automation artifacts
- **Size handling**: Extended buffer capture works with any output size
- **Scrollback immunity**: Markers work regardless of terminal scrolling

### Error Handling and Troubleshooting

#### Common Issues and Solutions

**❌ Problem: Commands not executing**
```
Symptoms: Nothing happens when Claude runs automation
```
**✅ Solutions:**
- Verify tmux session: `echo $TMUX` should show session path
- Check pane targeting: `tmux list-panes` to confirm pane numbers
- Confirm SSH connection: Ensure you see server prompt, not local prompt
- Test manual command: Try `tmux send-keys -t 1 "ls" Enter` manually

**❌ Problem: Incomplete output capture**
```
Symptoms: Command output is cut off or missing
```
**✅ Solutions:**
- Increase timeout: Commands may need more time to complete
- Check markers: Ensure START and END markers both appear in terminal
- Verify buffer size: `-S -5000` should handle most outputs
- Test single command: Try one command manually to verify timing

**❌ Problem: Mixed output in results**
```
Symptoms: Output contains multiple command results or extra text
```
**✅ Solutions:**
- Wait for prompt: Ensure previous command finished before new automation
- Use unique markers: Timestamp-based markers prevent conflicts
- Check timing: Adjust sleep values for server response characteristics
- Clear terminal: Start with clean prompt before automation

**❌ Problem: SSH connection lost**
```
Symptoms: Automation fails after working previously
```
**✅ Solutions:**
- Reconnect manually: Re-establish SSH connection in same pane
- Check connectivity: Verify network connection and server availability
- Update pane number: SSH reconnection may change pane assignments
- Restart if needed: Create new pane and reconnect if connection unstable

#### Debugging Techniques

**Step-by-step debugging:**
1. **Verify environment**: `echo $TMUX` and `tmux list-panes`
2. **Test basic automation**: Simple `ls` command with marker pattern
3. **Check timing**: Increase sleep values if commands seem to timeout
4. **Verify markers**: Look for START/END markers in terminal output
5. **Manual testing**: Try `tmux send-keys` commands manually first

**Diagnostic commands:**
```bash
# Test basic tmux targeting
tmux send-keys -t 1 "echo test" Enter

# Check pane activity
tmux list-panes -F "#{pane_id}: #{pane_current_command}"

# Verify marker appearance
tmux capture-pane -t 1 -p | tail -10
```

### Multi-Server Management

#### Concurrent Session Patterns
```bash
# Server 1 in pane 1
tmux send-keys -t 1 "hostname" Enter

# Server 2 in pane 2
tmux send-keys -t 2 "hostname" Enter

# Different timing for different servers
# Fast server: sleep 2
# Slow server: sleep 5
```

#### Organization Strategies
- **Descriptive pane names**: Use `tmux rename-window server-name`
- **Consistent pane assignment**: Document which pane connects to which server
- **Environment tracking**: Keep notes on server-specific characteristics
- **Session documentation**: Document multi-server session layouts

#### Scaling Considerations
- **Window management**: Use multiple tmux windows for different server groups
- **Pane limits**: Consider tmux pane limits when managing many servers
- **Context switching**: Plan efficient workflows for multi-server operations
- **Resource tracking**: Monitor local machine resources with many SSH connections

### Integration with Development Workflows

#### Version Control Integration
- **Documentation**: Keep server-specific automation scripts in version control
- **Environment configs**: Store server connection details in encrypted configs
- **Team sharing**: Share automation patterns through documentation
- **Change tracking**: Document automation script evolution

#### Monitoring Integration
- **Log correlation**: Link automated command outputs with monitoring systems
- **Alert context**: Use automation to gather context for alerts
- **Incident response**: Pre-built automation for common incident scenarios
- **Health checks**: Automated server health assessment workflows

## Related Documentation

This framework complements other automation and debugging approaches:

- **Server-specific guides**: Build upon this framework for environment-specific workflows
- **Monitoring integration**: Combine with monitoring tools for comprehensive server management  
- **Incident response**: Use as foundation for rapid incident investigation
- **Development workflows**: Integrate with deployment and debugging processes

## Summary

This Universal tmux SSH Automation Framework provides:

### Core Capabilities
✅ **Server-agnostic automation** - Works with any SSH-accessible server  
✅ **Security-conscious design** - Manual authentication with automated execution  
✅ **Proven reliability** - Marker-based patterns immune to terminal quirks  
✅ **Comprehensive templates** - Ready-to-use patterns for common tasks  
✅ **Systematic discovery** - Structured approaches to understand new environments  
✅ **Production-ready** - Robust error handling and troubleshooting guidance

### Key Benefits
- **Efficiency**: Eliminate manual command copying and execution
- **Consistency**: Standardized automation patterns across all servers  
- **Reliability**: Proven techniques that work regardless of server characteristics
- **Security**: Manual authentication maintains security boundaries
- **Scalability**: Support for concurrent multi-server management
- **Flexibility**: Easily adaptable to any server environment or use case

### Usage Philosophy
This framework treats automation as a **force multiplier for human expertise**, not a replacement. You remain in control of authentication, server selection, and security decisions, while the framework handles the repetitive execution and formatting tasks that slow down server administration and debugging.

**Perfect for**: System administrators, DevOps engineers, developers, security analysts, and anyone who regularly works with multiple servers through SSH connections.
