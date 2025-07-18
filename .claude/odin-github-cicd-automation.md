---
description: "ODIN GitHub CI/CD Server - Branch Debugging Automation"
---

# ODIN GitHub CI/CD Server Automation

**Server**: `odin.shopsys.cloud`  
**Purpose**: GitHub Actions CI/CD server for building and debugging preview branches  
**Access**: `ssh -p 4010 {surname}@odin.shopsys.cloud`

## Overview

This server hosts GitHub Actions runners that build preview branches. Each branch gets its own directory and can be debugged as if working locally with Docker Compose.

**Server Details:**
- **Hostname**: `odin.shopsys.cloud`
- **Access Pattern**: Individual developer accounts → `github-runner` user
- **Branch Location**: `~/actions-runner/_work/shopsys/shopsys/`
- **Branch Structure**: Each branch has its own subdirectory

## Server-Specific Commands

> **Note**: Use these commands with the [Universal tmux SSH Automation Framework](tmux-ssh-automation-framework.md). The framework handles command execution, output capture, and error handling.

### Initial Setup Commands
```bash
# Switch to service account
sudo su github-runner

# Navigate to branches directory
cd ~/actions-runner/_work/shopsys/shopsys/

# List available branches
ls -la

# Check system info
docker --version && docker compose version
```

### Branch Operations
```bash
# Enter specific branch
cd {branch-name} && pwd

# Check branch status
git status && git branch

# Check current branch containers
docker compose ps

# Start branch environment
docker compose up -d
```

### Container Logging (Hybrid Approach)

**From Parent Directory (Global View):**
```bash
# Check all running containers
docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'

# Shared services logs
docker logs --tail 50 github-runner-postgres-1
docker logs --tail 50 github-runner-redis-1  
docker logs --tail 50 github-runner-elasticsearch-1

# Branch-specific container logs
docker logs --tail 50 {branch-name}-webserver-1
docker logs --tail 50 {branch-name}-php-fpm-1
docker logs --tail 50 {branch-name}-storefront-1
```

**From Branch Directory (Focused View):**
```bash
# All branch services logs
docker compose logs --tail 20

# Specific service logs
docker compose logs --tail 50 webserver
docker compose logs --tail 50 php-fpm  
docker compose logs --tail 50 storefront

# Follow logs in real-time
docker compose logs -f webserver
```

## Standard Workflow

### 1. Initial Setup (Run Once Per Session)
1. Switch to GitHub runner user
2. Navigate to branches directory  
3. List available branches

### 2. Branch Debugging
1. Enter target branch directory
2. Check git status and Docker environment
3. Start services if needed
4. Monitor logs for issues

## Technical Details

**System Info:**
- OS: Linux (GitHub Actions runner)
- Docker: v28.0.4, Compose v2.34.0
- Multiple concurrent branch environments

**Container Architecture:**
- Base Path: `/home/github-runner/actions-runner/_work/shopsys/shopsys/`
- **Shared Services**: `github-runner-postgres-1`, `github-runner-redis-1`, `github-runner-elasticsearch-1`
- **Branch Services**: `{branch-name}-webserver-1`, `{branch-name}-php-fpm-1`, `{branch-name}-storefront-1`
- Each branch: isolated Docker stack with shared database layer

## Access Information

**SSH Connection:**
```bash
ssh -p 4010 {your-surname}@odin.shopsys.cloud
sudo su github-runner
cd ~/actions-runner/_work/shopsys/shopsys/
```

**Usage:**
- Each branch = separate directory + isolated environment
- Work like locally: `docker compose up`, logs, debugging
- Used for GitHub Actions build debugging and preview environments

---

**Framework Integration:** This document works with the [Universal tmux SSH Automation Framework](tmux-ssh-automation-framework.md) for complete automation patterns, troubleshooting, and advanced usage.