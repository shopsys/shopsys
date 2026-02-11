# Setup Playwright CLI

## Installation

```bash
npm install -g @playwright/cli@latest
playwright-cli --version
```

## Workspace initialization

Run in each project root where browser automation is needed:

```bash
playwright-cli install --skills
```

Creates `.claude/skills/playwright-cli/` with SKILL.md and references.

## Configuration

Optional `playwright-cli.json` in project root:

```json
{
  "headless": true,
  "viewport": { "width": 1280, "height": 720 }
}
```

Environment variables:

- `PLAYWRIGHT_CLI_SESSION` - default session name
- `PLAYWRIGHT_MCP_USER_DATA_DIR` - browser profile directory
- `PLAYWRIGHT_MCP_VIEWPORT_SIZE` - viewport dimensions (e.g. `1920x1080`)

## Troubleshooting

**Browser not found**: Re-run `playwright-cli install --skills` to detect browsers.

**Permission issues**: Verify global npm prefix is on PATH (`npm config get prefix`).

**Session conflicts**: Run `playwright-cli kill-all` to terminate stale sessions.
