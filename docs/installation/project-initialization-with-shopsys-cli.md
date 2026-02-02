# Project Initialization with Shopsys CLI

Shopsys CLI (package name: `shopsys/cli`) is a command-line tool that automates the initialization and configuration of new Shopsys Platform projects.
It simplifies multi-domain setup by providing interactive configuration prompts or YAML-based configuration files.

> **Important:** The Shopsys CLI version is coupled with the Shopsys Platform version. Always use the CLI version that matches your target Platform version (e.g., use CLI 18.0 for Platform 18.0).

## Installation

> **Windows Users:** The Shopsys CLI must be run inside WSL2 (Windows Subsystem for Linux). Open your WSL2 terminal before running these commands.

### Automatic Installation (Recommended)

When you create a new Shopsys Platform project using `composer create-project`, the CLI is **automatically downloaded** and the configuration wizard is launched:

```bash
composer create-project shopsys/project-base --no-install --keep-vcs --ignore-platform-reqs
```

The `post-create-project-cmd` hook downloads `shopsys.phar` and runs `php shopsys.phar configure .` automatically.

### Manual Installation

If you need to download the CLI manually (e.g., to use with an existing project), download the PHAR file from the [shopsys/cli releases](https://github.com/shopsys/cli/releases):

```bash
# Download the latest release
curl -L https://github.com/shopsys/cli/releases/latest/download/shopsys.phar -o shopsys
chmod +x shopsys
```

## Commands

### Initialize a New Project

The `init` command clones the project-base repository and runs the configuration wizard:

```bash
# Interactive mode (recommended)
./shopsys init my-project

# With a specific version (use matching CLI version!)
./shopsys init my-project --branch=18.0.0

# Non-interactive mode with YAML configuration
./shopsys init my-project --config=project-config.yaml

# Use the latest stable release (default)
./shopsys init my-project --branch=stable
```

**Options:**

- `--branch` (`-b`): Git branch or tag to clone (default: `stable`)
- `--config` (`-c`): Path to YAML configuration file for non-interactive mode

### Configure an Existing Project

The `configure` command applies configuration to an existing project-base:

```bash
# Interactive mode
./shopsys configure /path/to/project

# Non-interactive mode
./shopsys configure /path/to/project --config=project-config.yaml
```

### List Available Workers

View all configuration workers and their descriptions:

```bash
./shopsys workers
```

## Interactive Configuration

When running in interactive mode, the CLI will guide you through configuring your domains and project settings step by step.
After collecting all information, it displays a YAML summary and asks for confirmation before applying changes.

## YAML Configuration

For automated or repeatable setups, you can provide a YAML configuration file instead of using interactive mode.
Run the interactive mode once to see the expected YAML structure, then save it for future use:

```bash
./shopsys init my-project --config=project-config.yaml
```

## Next Steps

After running the CLI:

1. **Review changes**: Check the generated configuration files
2. **Commit to git**: Save your configuration
3. **Install the project**: Run `./scripts/install.sh`
4. **Start development**
