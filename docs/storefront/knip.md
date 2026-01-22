# Knip - Unused Code Detection

Knip is a tool that finds unused files, dependencies, and exports in your codebase. It helps keep the codebase clean by detecting dead code before it accumulates.

## Running Knip

To run Knip locally:

```bash
docker compose exec storefront pnpm run knip
```

## What Knip Detects

Knip finds several types of issues:

| Issue Type                | Description                                                             |
| ------------------------- | ----------------------------------------------------------------------- |
| **Unused files**          | Files that are not imported anywhere                                    |
| **Unused dependencies**   | Packages in `package.json` that are not used                            |
| **Unused exports**        | Exported functions, types, or variables that are not imported elsewhere |
| **Unlisted dependencies** | Packages used in code but not listed in `package.json`                  |

## Example Output

When Knip finds issues, it outputs them like this:

```
Unused exports (2)
SelectProps           type      components/Forms/Select/Select.tsx:13:13
formatUserName        function  utils/formatting/user.ts:5:14

Unused files (1)
utils/helpers/oldHelper.ts
```

## Fixing Issues

### Unused Exports

If you have an unused export, you have three options:

**1. Remove the `export` keyword** (most common)

```tsx
// Before
export type SelectProps = { ... }

// After
type SelectProps = { ... }
```

**2. Actually use the export** - Import it where needed

**3. Mark as public API** - If the export is intentionally public (e.g., for external consumers), add a `@public` JSDoc tag:

```tsx
/** @public */
export type SelectProps = { ... }
```

### Unused Files

Delete the file if it's truly not needed. If Knip incorrectly flags a file, check if it should be added to the `ignore` list in `knip.json`.

### Unused Dependencies

Remove the package from `package.json`:

```bash
docker compose exec storefront pnpm remove package-name
```

If the dependency is used but Knip can't detect it (e.g., used in shell scripts), add it to `ignoreDependencies` in `knip.json`.

## Configuration

Knip configuration is in `project-base/storefront/knip.json`. Key sections:

```json
{
    "ignoreIssues": {
        "**/*.generated.tsx": ["exports", "types"]
    },
    "project": ["**/*.{ts,tsx}"],
    "ignore": [".next/**", "cypress/**", "..."],
    "ignoreDependencies": ["..."],
    "next": true,
    "vitest": { ... },
    "eslint": true,
    "postcss": true,
    "graphql-codegen": { ... }
}
```

### Configuration Options

| Option               | Description                                            |
| -------------------- | ------------------------------------------------------ |
| `ignore`             | Files/folders to completely skip                       |
| `ignoreDependencies` | Dependencies Knip can't detect (e.g., used in scripts) |
| `ignoreIssues`       | Filter specific issue types for file patterns          |
| `ignoreUnresolved`   | Imports that can't be resolved but are valid           |

### Plugins

Knip uses plugins to understand framework-specific patterns:

- **next** - Detects Next.js pages, middleware, instrumentation
- **vitest** - Detects test files and setup
- **eslint** - Detects ESLint config usage
- **postcss** - Detects PostCSS config usage
- **graphql-codegen** - Detects GraphQL codegen config

## CI Integration

Knip runs automatically in CI pipelines (GitLab CI and GitHub Actions). If unused code is detected, the pipeline will fail with a clear message showing what needs to be fixed.

This ensures that:

- No dead code is merged into the codebase
- Dependencies stay clean and minimal
- Exports are intentional and used

## Further Reading

- [Knip Documentation](https://knip.dev/)
- [Knip Plugins Reference](https://knip.dev/reference/plugins)
