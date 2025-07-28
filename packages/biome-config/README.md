# @shopsys/biome-config

Shared Biome configuration for Shopsys Platform.

## Usage

Install the package as a dev dependency:

```bash
npm install --save-dev @shopsys/biome-config
```

Create a `biome.json` file in your project that extends this configuration:

```json
{
    "extends": ["@shopsys/biome-config"]
}
```

## Overriding Configuration

You can override any settings by adding them to your local `biome.json`:

```json
{
    "extends": ["@shopsys/biome-config"],
    "files": {
        "includes": ["src/**/*.js", "!src/vendor/**"]
    }
}
```

## Shared Configuration

This package includes the following Biome settings:

- **Formatter**: 4 spaces, 120 line width, LF line endings
- **Linter**: Recommended rules with specific adjustments for Shopsys codebase
- **JavaScript**: Single quotes, semicolons, trailing commas

See `index.json` for the complete configuration.
