---
description: "TMux SSH automation for ODIN cloud environment debugging and command execution"
---

# TMux SSH Automation Guide - ODIN Environment

This guide covers using tmux to automate SSH command execution and result capture for ODIN cloud environment debugging. This workflow enables efficient remote debugging with automated command execution and clean output capture for the Shopsys ODIN cloud infrastructure.

## Overview

Traditional production debugging requires manually executing commands over SSH and copying results. This guide documents an automation workflow using tmux that allows:

- **Automated command execution** in remote SSH sessions
- **Clean result capture** without manual copying
- **Consistent timing** for command completion
- **Reusable patterns** for common debugging tasks

## Prerequisites

**MANDATORY VERIFICATION**: You must already be working in an active TMax session before using this guide.

### Required Access and Setup
- **Individual ODIN cloud account**: SSH access to ODIN cloud (`ssh -p 4010 {your-username}@odin.shopsys.cloud`)
- **Active TMax session**: This workflow requires you to already be working in tmux
- **TMax verification**: Run `echo $TMUX` - you should see a tmux socket path, not empty output
- Basic familiarity with [production debugging workflows](debugging-crons-guide.md)

### TMax Session Verification

**STOP**: Before proceeding, verify you're in TMax:

```bash
# Check if you're in TMax session
echo $TMUX
# Expected output: /tmp/tmux-{uid}/default,{session-id},0
# If empty: You're NOT in TMax - start TMax first before using this guide
```

**If not in TMax**: This guide will NOT work outside of tmux. Start a TMax session first:
```bash
tmux new-session -s debugging
```

**Important**: This guide provides automation patterns for remote command execution within an established TMax environment. It does not cover TMax session management - you must already be working in TMax.

## TMux SSH Automation Workflow

### 1. Manual Setup Phase

**Important**: This section requires manual user interaction. Claude will NOT automate the SSH login for security reasons.

#### Step 1: Create New Pane (YOU DO THIS)

Create a new tmux pane for your ODIN SSH session:

```bash
# Create horizontal split pane
tmux split-window -h
```

#### Step 2: SSH to ODIN Cloud (YOU DO THIS)

In the NEW pane, SSH to your ODIN account:

```bash
# SSH to ODIN cloud with your individual account
ssh -p 4010 {your-username}@odin.shopsys.cloud
```

**Replace `{your-username}` with your actual ODIN username.**

#### Step 3: Confirm Login (TELL CLAUDE)

Once you've successfully logged into ODIN:
1. Verify you see the ODIN server prompt
2. Confirm which tmux pane contains your SSH session
3. Tell Claude: "I'm logged into ODIN in pane X" (where X is your pane number)

#### Step 4: Automation Ready

**Note**: The automation commands below assume your SSH session is in **pane 1**. If your SSH session is in a different pane, verify with `tmux list-panes` and adjust the pane number in automation commands accordingly.

### 2. User Interaction & Automation Boundaries

**What YOU Do Manually:**
- ✅ **TMax session management** - Start/manage your tmux sessions
- ✅ **Pane creation** - Create new tmux panes as needed  
- ✅ **SSH authentication** - Login to your individual ODIN account
- ✅ **Security confirmation** - Verify successful login before proceeding
- ✅ **Pane identification** - Tell Claude which pane contains your SSH session

**What CLAUDE Automates:**
- 🤖 **Command execution** - Send commands to your SSH pane automatically
- 🤖 **Output capture** - Capture and filter command results cleanly
- 🤖 **Timing management** - Handle command completion timing automatically
- 🤖 **Result formatting** - Present clean output without manual copying

**Critical Handoff Point:**
Once you've confirmed "I'm logged into ODIN in pane X", Claude takes over command execution while you remain in control of the SSH session.

### 3. Basic Command Execution Pattern

Send commands to the SSH pane and capture results:

```bash
# Send command to SSH pane (pane 1)
tmux send-keys -t 1 "pwd" Enter

# Wait for command completion
sleep 2

# Capture and display output
tmux capture-pane -t 1 -p
```

### 3. Enhanced Automation Pattern

For cleaner output that filters only new command results:

```bash
# Capture current line count
before_lines=$(tmux capture-pane -t 1 -p | wc -l)

# Send command
tmux send-keys -t 1 "ls -la" Enter

# Wait for completion
sleep 2

# Calculate and capture only new output
after_lines=$(tmux capture-pane -t 1 -p | wc -l)
tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

### 4. Improved Marker-Based Pattern (Recommended)

The line-counting method can be unreliable in certain scenarios (scrolling, wrapped lines, dynamic prompts). A more robust approach uses unique markers:

```bash
# Generate unique marker
marker="TMUX_MARKER_$(date +%s%N)"

# Send marker before command
tmux send-keys -t 1 "echo '=== $marker START ==='" Enter
sleep 0.5

# Send actual command
tmux send-keys -t 1 "ls -la" Enter

# Wait for completion
sleep 2

# Send marker after command
tmux send-keys -t 1 "echo '=== $marker END ==='" Enter
sleep 0.5

# Capture output between markers
tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

**Why this approach is better:**
- **Immune to scrolling**: Markers persist regardless of terminal buffer changes
- **Handles wrapped lines**: No dependency on line counting
- **Works with dynamic prompts**: Markers clearly delineate command boundaries

### 5. Critical Timing Pattern Understanding

**IMPORTANT**: The sleep timing in marker-based automation is crucial:

```bash
# CORRECT timing flow:
tmux send-keys -t 1 "echo '=== MARKER START ==='" Enter
sleep 0.5                    # Small delay after start marker
tmux send-keys -t 1 "COMMAND" Enter  
sleep 3                      # WAIT for command to complete in target pane
tmux send-keys -t 1 "echo '=== MARKER END ==='" Enter  # Send end marker AFTER command finishes
sleep 0.5                    # Small delay before capture
tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== MARKER START ===/,/=== MARKER END ===/p" | sed '1d;$d'
```

**Key insight**: The sleep happens in the **active pane** (where you run the automation) to wait for the **target pane's command** to complete. The end marker must be sent **immediately after** the command finishes (after the sleep), not delayed by the sleep.

### 6. Enhanced One-Liner for Long Output Commands

**CRITICAL**: For commands with long output that may exceed visible screen buffer:

```bash
# ENHANCED one-liner with extended buffer capture
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "COMMAND" Enter; sleep 5; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

**Key improvements over basic one-liner**:
- **Extended buffer**: `-S -5000` captures last 5000 lines of scrollback history
- **Longer sleep**: 5 seconds ensures command completion for complex operations
- **Universal compatibility**: Works for ANY command output length
- **Maintains marker reliability**: Both START and END markers always captured

**IMPORTANT: No Context Overload**
The `-S -5000` buffer capture does NOT overload your context:
- **Shell processes 5000 lines locally** - all filtering happens in the pipeline
- **Only filtered content reaches context** - just the command output between markers
- **No performance impact** - sed pipeline is very efficient
- **Safe to use large buffer sizes** - only relevant data crosses the context boundary

**When to use**: This should be the **standard pattern** for all production automation. The extended buffer ensures reliability without context cost.
- **Robust filtering**: `sed` extracts exact content between markers
- **Easy debugging**: Markers are visible in terminal for manual verification

## Common Production Debugging Commands

### Navigation and Exploration

#### Using Improved Marker-Based Method

```bash
# Check current directory
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "pwd" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# List directory contents
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "ls -la" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Navigate to web directory
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "cd /var/www" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Legacy Line-Counting Method (For Reference)

```bash
# Check current directory (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "pwd" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# List directory contents (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "ls -la" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Navigate to web directory (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "cd /var/www" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

### Docker Container Access

#### Using Improved Marker-Based Method

```bash
# List running containers
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker ps" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Access PHP container (note: this changes the shell context)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm bash" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Legacy Line-Counting Method (For Reference)

```bash
# List running containers (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker ps" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Access PHP container (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm bash" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

### Log File Examination

#### Using Improved Marker-Based Method

```bash
# Check recent cron logs
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "tail -20 /var/www/logs/cron-product.log" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check PHP error logs
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec production-php-fpm tail -20 /var/log/php-fpm/www-error.log" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Legacy Line-Counting Method (For Reference)

```bash
# Check recent cron logs (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "tail -20 /var/www/logs/cron-product.log" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Check PHP error logs (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec production-php-fpm tail -20 /var/log/php-fpm/www-error.log" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

### Elasticsearch Debugging

#### Finding External Elasticsearch Configuration

Production servers often use external Elasticsearch clusters. To discover the configuration:

```bash
# Check environment variables for Elasticsearch settings (marker-based method)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i elastic" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

This will reveal key configuration like:
- `ELASTICSEARCH_HOST={odin-elasticsearch-host}` (external host IP - to be discovered)
- `ELASTICSEARCH_INDEX_PREFIX={odin-environment-prefix}` (index naming pattern - to be discovered)

**Legacy Example (LUNZO Environment - For Reference):**
- `ELASTICSEARCH_HOST=10.80.125.31` (external host IP)
- `ELASTICSEARCH_INDEX_PREFIX=lunzo_prod` (index naming pattern)

#### Elasticsearch Cluster Operations

##### Using Improved Marker-Based Method

```bash
# Check cluster health (replace {elasticsearch-host} with discovered ODIN host)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://{elasticsearch-host}:9200/_cluster/health?pretty'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# List all indices with sizes
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://{elasticsearch-host}:9200/_cat/indices?v'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Count documents in specific index (replace with actual ODIN index name after discovery)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://{elasticsearch-host}:9200/{odin-index-name}/_count?pretty'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

**Legacy Examples (LUNZO Environment - For Reference):**
```bash
# LUNZO cluster health
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/_cluster/health?pretty'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# LUNZO document count
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_count?pretty'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

##### Legacy Line-Counting Method (For Reference)

```bash
# Check cluster health (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/_cluster/health?pretty'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# List all indices with sizes (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/_cat/indices?v'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Count documents in specific index (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_count?pretty'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

#### Elasticsearch Data Operations

##### Using Improved Marker-Based Method

```bash
# Search products in domain 1 (first 5 results)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_search?size=5&pretty'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Search for specific product by name
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl -X GET 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_search?pretty' -H 'Content-Type: application/json' -d '{\"query\":{\"match\":{\"name\":\"product_name\"}}}'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Check index settings and mappings
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_settings?pretty'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

##### Legacy Line-Counting Method (For Reference)

```bash
# Search products in domain 1 (first 5 results) (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_search?size=5&pretty'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Search for specific product by name (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl -X GET 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_search?pretty' -H 'Content-Type: application/json' -d '{\"query\":{\"match\":{\"name\":\"product_name\"}}}'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Check index settings and mappings (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl 'http://10.80.125.31:9200/lunzo_prod20241217112015_prod_product_1/_settings?pretty'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

## External Service Discovery

### Systematic Approach to Finding External Services

Production environments often use external services (Redis, Elasticsearch, databases) that aren't visible in local Docker containers. Use this systematic approach:

#### 1. Check Environment Variables

##### Using Improved Marker-Based Method
```bash
# Check for service-specific configuration
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i redis" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i elastic" Enter; sleep 2; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i database" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

##### Legacy Line-Counting Method (For Reference)
```bash
# Check for service-specific configuration (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i redis" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i elastic" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm env | grep -i database" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

#### 2. Test External Service Connectivity

##### Using Improved Marker-Based Method
```bash
# Test Redis connection (example: 10.80.125.31:6379)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm php -r \"\\\$r = new Redis(); \\\$r->connect('10.80.125.31', 6379); echo 'Redis connected: ' . (\\\$r->ping() ? 'OK' : 'FAIL') . PHP_EOL;\"" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Test Elasticsearch connection
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "curl -m 5 'http://10.80.125.31:9200/_cluster/health'" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

##### Legacy Line-Counting Method (For Reference)
```bash
# Test Redis connection (example: 10.80.125.31:6379) (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm php -r \"\\\$r = new Redis(); \\\$r->connect('10.80.125.31', 6379); echo 'Redis connected: ' . (\\\$r->ping() ? 'OK' : 'FAIL') . PHP_EOL;\"" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Test Elasticsearch connection (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "curl -m 5 'http://10.80.125.31:9200/_cluster/health'" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

#### 3. Investigate Service Configuration

##### Using Improved Marker-Based Method
```bash
# Check application configuration files for service references
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm find /var/www -name '*.yml' -o -name '*.yaml' | head -10" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'

# Search for specific service references in config
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "docker exec -it production-php-fpm grep -r 'elasticsearch' /var/www/config/ | head -5" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

##### Legacy Line-Counting Method (For Reference)
```bash
# Check application configuration files for service references (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm find /var/www -name '*.yml' -o -name '*.yaml' | head -10" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))

# Search for specific service references in config (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "docker exec -it production-php-fpm grep -r 'elasticsearch' /var/www/config/ | head -5" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

### Discovery Results Examples

**ODIN Environment Configuration (To Be Discovered):**
- `ELASTICSEARCH_HOST={odin-elasticsearch-host}` (external cluster IP - to be discovered)
- `ELASTICSEARCH_INDEX_PREFIX={odin-environment-prefix}` (index naming pattern - to be discovered)
- `REDIS_HOST={odin-redis-host}` (external Redis server - to be discovered)
- `REDIS_PREFIX={odin-environment-prefix}` (key prefix pattern - to be discovered)

**Legacy Examples (LUNZO Environment - For Reference):**
- `ELASTICSEARCH_HOST=10.80.125.31` (external cluster IP)
- `ELASTICSEARCH_INDEX_PREFIX=lunzo_prod` (index naming pattern)
- `REDIS_HOST=10.80.125.31` (external Redis server)
- `REDIS_PREFIX=lunzo_prod` (key prefix pattern)

## Advanced Patterns

### SSH Exec Function Concepts

#### Improved Marker-Based Function (Recommended)

For repeated use, the improved marker-based pattern can be abstracted into a reusable function concept:

```bash
# Improved ssh_exec function using markers (inline pattern)
ssh_exec_marker() {
    local cmd="$1"
    local pane="${2:-1}"
    local timeout="${3:-10}"
    
    # Generate unique marker
    local marker="TMUX_MARKER_$(date +%s%N)"
    
    # Send start marker
    tmux send-keys -t "$pane" "echo '=== $marker START ==='" Enter
    sleep 0.5
    
    # Execute command
    tmux send-keys -t "$pane" "$cmd" Enter
    
    # Wait with smart timing
    local count=0
    while [ $count -lt $timeout ]; do
        # Check if prompt returned
        if tmux capture-pane -t "$pane" -p | tail -1 | grep -q '\$'; then
            break
        fi
        sleep 0.5
        count=$((count + 1))
    done
    
    # Send end marker
    tmux send-keys -t "$pane" "echo '=== $marker END ==='" Enter
    sleep 0.5
    
    # Return filtered output between markers
    tmux capture-pane -t "$pane" -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
}
```

#### Legacy Line-Counting Function (For Reference)

```bash
# Legacy ssh_exec function using line counting (inline pattern)
ssh_exec_legacy() {
    local cmd="$1"
    local pane="${2:-1}"
    local timeout="${3:-10}"
    
    # Capture current state
    local before_lines=$(tmux capture-pane -t "$pane" -p | wc -l)
    
    # Execute command
    tmux send-keys -t "$pane" "$cmd" Enter
    
    # Wait with smart timing
    local count=0
    while [ $count -lt $timeout ]; do
        local after_lines=$(tmux capture-pane -t "$pane" -p | wc -l)
        if [ $after_lines -gt $before_lines ]; then
            # Check if prompt returned
            if tmux capture-pane -t "$pane" -p | tail -1 | grep -q '\$'; then
                break
            fi
        fi
        sleep 0.5
        count=$((count + 1))
    done
    
    # Return filtered output
    tmux capture-pane -t "$pane" -p | tail -$((after_lines - before_lines))
}
```

### Database Query Execution

#### Using Improved Marker-Based Method

```bash
# Execute SQL queries (when in PHP container)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "php bin/console debug:container database_connection" Enter; sleep 3; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Legacy Line-Counting Method (For Reference)

```bash
# Execute SQL queries (when in PHP container) (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "php bin/console debug:container database_connection" Enter; sleep 2; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

## Integration with Existing Debugging Workflows

### Complement to Cron Debugging

This automation workflow enhances the [Production Cron Debugging Guide](debugging-crons-guide.md) by:

- **Automating command execution** instead of manual copy-paste
- **Faster iteration** through debugging steps
- **Consistent result capture** for analysis
- **Reduced human error** in command execution

### Redis Operations

Combines with [Redis access patterns](README.md#redis-access) for automated Redis debugging:

#### Using Improved Marker-Based Method

```bash
# Automated Redis key listing (replace with discovered ODIN Redis host and prefix)
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "php -r \"\$r = new Redis(); \$r->connect('{redis-host}', 6379); print_r(\$r->keys('{redis-prefix}*'));\"" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

**Legacy Example (LUNZO Environment - For Reference):**
```bash
# LUNZO Redis key listing
marker="TMUX_MARKER_$(date +%s%N)"; tmux send-keys -t 1 "echo '=== $marker START ==='" Enter; sleep 0.5; tmux send-keys -t 1 "php -r \"\$r = new Redis(); \$r->connect('10.80.125.31', 6379); print_r(\$r->keys('lunzo_prod*'));\"" Enter; sleep 4; tmux send-keys -t 1 "echo '=== $marker END ==='" Enter; sleep 0.5; tmux capture-pane -t 1 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

#### Legacy Line-Counting Method (For Reference)

```bash
# Automated Redis key listing (legacy method)
before_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux send-keys -t 1 "php -r \"\$r = new Redis(); \$r->connect('10.80.125.31', 6379); print_r(\$r->keys('lunzo_prod*'));\"" Enter; sleep 3; after_lines=$(tmux capture-pane -t 1 -p | wc -l); tmux capture-pane -t 1 -p | tail -$((after_lines - before_lines))
```

## Best Practices

### Timing Considerations

- **Simple commands**: 3-second sleep recommended for reliability
- **Docker exec**: 4-second sleep for container access
- **Database operations**: 4-5 seconds depending on query complexity
- **Long-running commands**: Consider monitoring rather than fixed timing
- **Elasticsearch operations**: 4-5 seconds for search queries

### Error Handling

- **Verify SSH connection** before automation
- **Check tmux pane targeting** (use `tmux list-panes` to confirm)
- **Monitor for command failures** in captured output
- **Use longer timeouts** for complex operations

### Security Considerations

- **Never automate destructive commands** without explicit verification
- **Use read-only operations** for initial exploration
- **Verify command parameters** before execution
- **Document all automated actions** for audit trail

## Troubleshooting

### Common Issues

**Commands not executing:**
- Check tmux pane numbering with `tmux list-panes`
- Verify SSH connection is active
- Ensure proper shell prompt in target pane

**Output capture incomplete:**
- Increase sleep duration for command completion
- Check if command requires user interaction
- Verify tmux capture-pane targeting

**Mixed output in results:**
- Use marker-based method for reliable filtering
- Ensure commands complete before next capture
- Consider command output length in timing

**Line-counting method issues:**
- **Terminal scrolling**: Buffer changes affect line counts unpredictably
- **Long output**: Commands with extensive output may wrap lines or trigger scrolling
- **Dynamic prompts**: PS1 changes or multi-line prompts cause miscounting
- **Async output**: Background processes may add lines between measurements
- **Solution**: Use marker-based method instead for consistent results

## Related Documentation

- **[Production Cron Debugging Guide](debugging-crons-guide.md)** - Manual debugging workflows that complement this automation
- **[Production README](README.md)** - Basic SSH access and Redis operations
- **[Development Playwright Admin Access](../Development/Playwright-Admin-Access.md)** - Alternative automation for admin interface debugging

## Summary of Improvements

### Key Changes in This ODIN Version

1. **Environment Transformation**: Updated from LUNZO to ODIN cloud environment
2. **Manual Setup Phase**: Added secure manual SSH authentication workflow  
3. **TMax Verification**: Mandatory TMax session checking before automation
4. **User Interaction Boundaries**: Clear separation of manual vs. automated tasks
5. **Legacy Preservation**: LUNZO examples preserved for reference and gradual refactoring
6. **Placeholder System**: ODIN-specific values to be discovered and updated

### Environment Discovery Approach

**Gradual Refactoring Strategy:**
- **Phase 1 Complete**: Core workflow updated for ODIN environment
- **Phase 2 Ongoing**: Environment-specific values (IPs, hostnames, prefixes) marked with placeholders
- **Phase 3 Planned**: Replace placeholders with actual ODIN values as we explore

**Discovery Workflow:**
1. Use environment variable discovery commands to find ODIN-specific configurations
2. Update placeholders with real values: `{elasticsearch-host}` → actual IP
3. Test and validate commands with real ODIN infrastructure  
4. Keep legacy examples for comparison and fallback reference

### Core Automation Improvements (Preserved from Original)

1. **Marker-Based Method**: Added robust marker-based output capture that is immune to terminal scrolling and line wrapping issues
2. **Line-Counting Issues Documented**: Identified and documented specific problems with the original line-counting approach
3. **Dual Examples**: All sections now provide both the improved marker-based method and legacy line-counting method for comparison
4. **Better Reliability**: The marker approach provides consistent results regardless of terminal buffer state, command output length, or prompt variations

### Recommendations

- **Primary Method**: Use the marker-based approach for all new automation scripts
- **Legacy Support**: Keep line-counting examples for reference and backward compatibility
- **Debugging**: Markers are visible in terminal output, making debugging easier
- **Migration**: Gradually migrate existing scripts from line-counting to marker-based approach

## Notes

- This workflow is designed for **production debugging only** - use appropriate caution
- Always verify commands before automation, especially UPDATE/DELETE operations
- The inline pattern approach avoids persistent function definitions while maintaining automation benefits
- Consider timing adjustments based on server load and command complexity
- **Prefer marker-based method** over line-counting for reliability and consistency