---
description: "tmux SSH automation guide for ODIN server GraphQL investigation"
created: "2025-07-18"
status: "Active Use"
---

# tmux SSH Automation Guide for ODIN Server

## Connection Status
- **Server**: `odin.shopsys.cloud`
- **User**: `github-runner`
- **Current tmux pane**: `%14`
- **Working directory**: `/home/github-runner/actions-runner/_work/shopsys/shopsys`
- **Current branch**: `jm-after-build-bug-fix-ssp-3495`

## Integration with Universal Framework

This guide uses the **Universal tmux SSH Automation Framework** documented in:
- `.claude/tmux-ssh-automation-framework.md` - Core automation patterns
- `.claude/odin-github-cicd-automation.md` - ODIN server specific commands

## GraphQL Investigation Automation Commands

### Standard Log Collection Pattern
```bash
# Basic investigation pattern
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "cd ~/actions-runner/_work/shopsys/shopsys/jm-after-build-bug-fix-ssp-3495" Enter
sleep 2
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### PHP Container Logs Investigation
```bash
# Collect PHP logs for GraphQL queries
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "docker compose logs php-fpm | grep -E '(PromotedCategories|SliderItems)' -A2 -B2" Enter
sleep 5
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Container Status Check
```bash
# Check container status
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "docker compose ps" Enter
sleep 3
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Branch-Specific Container Logs
```bash
# Branch-specific PHP logs
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "docker logs --tail 50 jm-after-build-bug-fix-ssp-3495-php-fpm-1" Enter
sleep 4
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Error Analysis
```bash
# Check for critical errors
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "docker compose logs php-fpm | grep -i error | tail -20" Enter
sleep 4
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

### Storefront Logs
```bash
# Collect storefront logs
marker="TMUX_MARKER_$(date +%s%N)"
tmux send-keys -t %14 "echo '=== $marker START ==='" Enter
sleep 0.5
tmux send-keys -t %14 "docker compose logs storefront | grep -E '(PromotedCategories|SliderItems)' -A2 -B2" Enter
sleep 5
tmux send-keys -t %14 "echo '=== $marker END ==='" Enter
sleep 0.5
tmux capture-pane -t %14 -p -S -5000 | sed -n "/=== $marker START ===/,/=== $marker END ===/p" | sed '1d;$d'
```

## Investigation Workflow

### Phase 1: Initial Assessment
1. **Container Status**: Check all containers are running
2. **Basic Connectivity**: Verify services are responding
3. **Recent Logs**: Check for immediate errors or warnings

### Phase 2: Targeted Log Collection
1. **PHP Logs**: Search for GraphQL query execution logs
2. **Storefront Logs**: Check frontend GraphQL query logs  
3. **Error Logs**: Look for specific error patterns

### Phase 3: Evidence Analysis
1. **Domain Information**: Extract domain ID and configuration
2. **Query Parameters**: Analyze query execution parameters
3. **Result Patterns**: Document empty vs populated results

### Phase 4: Hypothesis Validation
1. **Data Verification**: Check if expected data exists
2. **Timing Analysis**: Look for race conditions or timing issues
3. **Configuration Check**: Verify domain and service configuration

## Command Safety

All commands listed here are **✅ SAFE** (read-only operations):
- `docker compose logs` - View container logs
- `docker compose ps` - Check container status  
- `docker logs` - View specific container logs
- `grep` operations - Search through logs
- Navigation commands - Change directories

## Expected Log Patterns

### Successful Query Execution
```
🔍 [PromotedCategoriesQuery] Starting query execution
🔍 [PromotedCategories] Domain: CS (ID: 1)
🔍 [PromotedCategories] Query result count: 3
```

### Failed Query Execution  
```
🔍 [PromotedCategoriesQuery] Starting query execution
🔍 [PromotedCategories] Domain: CS (ID: 1)
🔍 [PromotedCategories] Query result count: 0
⚠️ [PromotedCategories] EMPTY RESULT - This is the issue!
```

## Integration with Investigation Plan

This automation guide directly supports the **Iterative Debugging Workflow** documented in:
- `.claude/root-cause-debugging-plan.md` - Main investigation plan
- `.claude/session-investigation-findings.md` - Current session findings

When user reports "logs are ready", I will use these automation patterns to collect and analyze evidence systematically.