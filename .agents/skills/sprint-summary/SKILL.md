---
name: sprint-summary
description: Generates a Czech sprint summary article from a Jira CSV export and can optionally prepare Playwright screenshots/videos as side attachments for relevant UX tasks.
---

# Sprint Summary

Generates a sprint summary from a Jira CSV export. Creates a structured article in Czech suitable for Confluence. If Playwright MCP is available, it can optionally prepare screenshots/videos for relevant UI/UX tasks as side attachments.

## Initial Setup

When invoked without arguments, respond:
```
I am ready to generate a sprint summary. Please provide:
1. Path to the CSV file exported from Jira
2. (Optional) Path where to save the output file

Example: /sprint-summary /home/user/jira-export.csv

Expected CSV columns:
- Issue key (e.g., SSP-3614)
- Summary
- Description
- Custom field (Merge Request) - GitHub PR link
```

Then wait for user input.

## Command Arguments

- **$1** (required): Path to Jira CSV export
- **$2** (optional): Path for output markdown file (default: same directory as CSV, filename sprint-summary.md)

## Workflow

### Step 1: Load and Parse CSV

1. Read the CSV file using Read tool
2. Expected columns:
   - Issue key - Ticket ID (e.g., SSP-3614)
   - Summary - Task name
   - Description - Detailed description
   - Custom field (Merge Request) - GitHub PR link

### Step 2: Categorize Tasks

Split tasks into categories based on name, description, and prefixes:

**New Features:**
- Tasks adding new functionality
- Keywords: "add", "new", "implement", "as a customer I want", "feature"

**Bug Fixes - Backend/Admin:**
- Bugs in PHP code, admin panel, API
- Keywords: "bug", "fix", "error", "[BE]", "Admin:", "chyba"

**Bug Fixes - Storefront:**
- Bugs in frontend (Next.js/React)
- Keywords: "[FE]", "storefront", components like "Tag", "form", "formulář"

**Admin Improvements:**
- UI/UX changes in admin panel
- Keywords: "admin template", "responsive", "design", "šablona administrace"

**Performance and Security:**
- Optimizations, security fixes
- Keywords: "performance", "security", "optimization", "cache", "výkon"

**Accessibility:**
- A11y fixes
- Keywords: "accessibility", "voiceover", "tabindex", "screen reader"

**Developer Experience (DX):**
- Developer tools, CI/CD, tests
- Keywords: "test", "CI", "GitHub Actions", "GitLab", "mutagen", "Docker", "Cypress"

**Demo Data and Documentation:**
- Changes in demo data, documentation
- Keywords: "[DOCS]", "demo data", "fixture", "documentation"

**Infrastructure:**
- Servers, environments, deployment
- Keywords: "Odin", "server", "infrastructure", "deployment"

### Step 3: Fetch PR Details (Optional)

For key tasks (new features, major changes), use Task tool with pr-diff-fetcher subagent to get PR descriptions:

```
Fetch PR descriptions for these GitHub PRs from shopsys/shopsys:
- PR #{number} ({task name})
Return just the PR title and description/body.
```

### Step 4: Generate Markdown File

Create a structured markdown in Czech. Do NOT split categories into Backend/Storefront subsections. Instead, add a platform prefix to task titles only when it is not obvious from the name:

**When to add prefix:**
- "Povýšení závislostí" -> "Storefront: Povýšení závislostí" (ambiguous, needs prefix)
- "Optimalizace broadcast channel" -> "Storefront: Optimalizace broadcast channel" (technical, needs context)
- "Oprava Cypress testů" -> "Storefront: Oprava Cypress testů" (Cypress = storefront tests)

**When NOT to add prefix:**
- "Zobrazení slev v košíku" (obviously storefront - cart UI)
- "Chyba v administraci feedů" (obviously backend - admin)
- "GraphQL schema změny" (obviously backend API)
- "Formulář se neskryje po odeslání" (obviously storefront - form UI)

Use format "Storefront: " or "Backend: " or "Admin: " as prefix when needed.

```markdown
# Shrnutí sprintu

## Nové funkce

#### {Task name with optional platform prefix}
- **Jira:** [SSP-XXXX](https://shopsys.atlassian.net/browse/SSP-XXXX) | **PR:** [#YYYY](https://github.com/shopsys/shopsys/pull/YYYY)
- Bullet point 1 describing the change
- Bullet point 2 describing the change

#### {Another task}
...

---

## Opravy chyb

#### {Task name}
...

---

## Vylepšení administrace

...

## Výkon a bezpečnost

...

## Přístupnost (Accessibility)

...

## Developer Experience (DX)

...

## Demo data a dokumentace

...

## Infrastruktura

...
```

### Step 5: Format Individual Items

For each task:

1. **Heading:** Use Summary from CSV or PR title (if more descriptive)
2. **Links:**
   - Jira: https://shopsys.atlassian.net/browse/{Issue key}
   - PR: Extract from Custom field (Merge Request)
3. **Description:**
   - Convert Description to bullet points
   - Remove Jira markup (noformat blocks, image embeds, wiki links)
   - Focus on:
     - What changed
     - Why it changed (if relevant)
     - Impact on users/developers
   - Max 3-5 bullet points per task

### Step 6: Save Markdown

1. Save the markdown file
2. Keep the markdown article clean and copy-friendly
3. Do not automatically embed screenshots into the markdown

### Step 7: Offer Visual Attachments (Optional)

After the markdown is generated, check whether Playwright MCP/browser tools are available and whether the application is reachable.

If yes, explicitly ask the user whether they want visual attachments for relevant tasks:
- The user may name concrete Jira tickets
- Or the user may let the agent choose relevant tasks

When choosing tasks automatically, prefer:
- Storefront/Admin UX changes
- Validation fixes
- Modal/z-index issues
- Skeleton/loading states
- Widget behavior
- Checkout and form interactions

Usually skip:
- Backend-only changes
- DX/infrastructure tasks
- API/internal refactors without visible UI impact

If the user wants visuals:
1. Use Playwright MCP only; do not install Playwright into the project
2. Save assets into a sibling directory next to the markdown output
3. Default asset directory name:
   - if output is `sprint-summary.md`, use `sprint-summary-assets/`
4. Naming convention:
   - `{ISSUE_KEY}-{short-slug}.png`
   - `{ISSUE_KEY}-{short-slug}.gif`
   - `{ISSUE_KEY}-{short-slug}.mp4`
5. Prefer full viewport screenshots over tight crops
6. Crop only when the full viewport is unusable or hides the relevant change
7. Use video/GIF only for interaction-heavy changes where a screenshot is insufficient
8. If a scenario cannot be reproduced, skip the asset and report that clearly

When assets are generated, update the markdown item only with a short textual reference, not an embedded image. Use this format:

```markdown
- Příloha: `sprint-summary-assets/SSP-3891-variant-parameters.png`
```

This keeps the article easy to preview in IDEs and easy to copy to Confluence.

### Step 8: Present Result

1. Display the generated markdown file path
2. If attachments were generated, mention the asset directory
3. Offer to open in editor (PhpStorm)
4. Display summary:

```
Sprint summary has been generated.

File: {path to file}
Assets: {path to assets directory, if any}

Statistics:
- Total tasks: XX
- New features: X
- Bug fixes: X (BE: X, FE: X)
- DX improvements: X
- ...

Would you like to open the file in PhpStorm?
```

## Rules for Describing Changes

1. **Write in Czech** - the entire article including technical terms (where it makes sense)
2. **Use bullet points** - not paragraphs
3. **Be concise** - max 1-2 sentences per bullet point
4. **Focus on impact** - what it means for users/developers
5. **Omit internal details** - implementation details only if relevant
6. **Keep technical terms** - do not translate GraphQL, API, Docker, etc.
7. **Keep article copy-friendly** - references to attachments are allowed, embedded media is not the default output

## Cleaning Jira Markup

When processing Description field, remove or convert Jira-specific markup:

- noformat blocks -> code block or omit
- image embeds (exclamation mark syntax) -> omit
- wiki links [text pipe url] -> markdown link [text](url)
- double curly braces for code -> inline code with backticks
- single asterisk bold -> double asterisk bold
- underscore italic -> single asterisk italic
- h4 dot heading -> markdown heading with hashes

## Example Output

```markdown
# Shrnutí sprintu

## Nové funkce

#### Upřesnění výpočtu lhůty pro odstoupení od smlouvy
- **Jira:** [SSP-3576](https://shopsys.atlassian.net/browse/SSP-3576) | **PR:** [#4317](https://github.com/shopsys/shopsys/pull/4317)
- Administrátor může nyní importovat státní svátky pro vybranou zemi a domény
- Lhůta pro odstoupení se automaticky posouvá na první pracovní den při víkendu/svátku
- Nová možnost označit den jako "Den pracovního volna" v nastavení

#### Storefront: Podpora onClick na komponentě Tag
- **Jira:** [SSP-3743](https://shopsys.atlassian.net/browse/SSP-3743) | **PR:** [#4322](https://github.com/shopsys/shopsys/pull/4322)
- Doplněn onClick handler pro napojení na GTM

---

## Opravy chyb

#### Formulář se neskryje po odeslání
- **Jira:** [SSP-3674](https://shopsys.atlassian.net/browse/SSP-3674) | **PR:** [#4303](https://github.com/shopsys/shopsys/pull/4303)
- Po odeslání se nyní skryje/vyčistí formulář na kontaktní stránce a stránkách osobních údajů
- Příloha: `sprint-summary-assets/SSP-3674-contact-form-state.png`

#### Admin: Chyba ve vyhledávání rozšířeného filtru
- **Jira:** [SSP-3748](https://shopsys.atlassian.net/browse/SSP-3748) | **PR:** [#4334](https://github.com/shopsys/shopsys/pull/4334)
- Opraveno dvojité volání requestu při použití našeptávání

---

## Výkon a bezpečnost

#### Storefront: Optimalizace broadcast channel
- **Jira:** [SSP-2013](https://shopsys.atlassian.net/browse/SSP-2013) | **PR:** [#4327](https://github.com/shopsys/shopsys/pull/4327)
- Při více otevřených záložkách se query na košík volá pouze jednou
```

## Audience

Target audience of the article:
- **Developers** - technical details, breaking changes, new APIs
- **Project managers** - overview of completed work, new features
- **Other colleagues** - high-level summary of changes

Write so that the article is understandable for non-technical colleagues, but also contains enough technical details for developers.
