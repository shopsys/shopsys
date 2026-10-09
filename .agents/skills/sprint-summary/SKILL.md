---
name: sprint-summary
description: Generates a Czech sprint summary article from Jira sprint data (Jira MCP first, CSV fallback), publishes it to Confluence with embedded screenshots and MP4 clips, and records the clips with Playwright MCP (visible cursor, ffmpeg post-processing).
---

# Sprint Summary

Generates a sprint summary from Jira sprint data. Prefer Jira MCP as the primary source; use a Jira CSV export only as a fallback when MCP is unavailable or missing required data. Creates a structured article in Czech suitable for Confluence. If Playwright MCP is available, it can optionally prepare screenshots/videos for relevant UI/UX tasks as side attachments.

## Initial Setup

When invoked without arguments, respond:
```
I am ready to generate a sprint summary. Please provide:
1. Sprint number or Jira sprint identifier
2. (Optional) Path to the CSV file exported from Jira, if Jira MCP is not available
3. (Optional) Path where to save the output file

Example: /sprint-summary 192
Fallback CSV example: /sprint-summary /home/user/jira-export.csv

You can generate the CSV here: https://shopsys.atlassian.net/issues/?filter=12564
Before exporting, update the sprint number in the filter to the sprint you want to summarize.
To export the CSV, click the three dots in the top right corner and select "Export" -> "CSV - filter fields".

Expected CSV columns:
- Issue key (e.g., SSP-3614)
- Summary
- Description
- Custom field (Merge Request) - GitHub PR link
```

Then wait for user input.

## Command Arguments

- **$1** (required): Sprint number/Jira sprint identifier, or path to Jira CSV export as fallback
- **$2** (optional): Path for output markdown file (default: `sprint-summary.md` or same directory as CSV)

## Workflow

### Step 1: Load Source Data and Select Tasks

Primarily use Jira MCP. CSV export is a fallback only when Jira MCP is unavailable or does not provide the required data.

When using Jira MCP:
1. Find the requested sprint. Do not rely on the saved filter 12564 (it points to an old sprint); query directly with `sprint = "SSP Sprint #<N>"` and page through all results (`nextPageToken`).
2. Include only issues that were completed in that sprint.
3. Load issue details, PR links, and comments. The Merge Request field is `customfield_10031`, the sprint field is `customfield_10020`.
4. Use comments especially for tasks without PRs, research tasks, and unclear changes.
5. If the user links the previous sprint article, read it first (an external `wiki/external/...` link is not fetchable by WebFetch; open it in the built-in browser and use `get_page_text`) and skip everything already described there.

Only include tasks completed in the sprint. Do not include issues that were merely assigned to or present in the sprint but not completed there.

Exclude operational/meta tasks such as sprint overhead, grooming, release management, coordination, or similar internal process tasks unless the user explicitly asks to include them.

When using CSV fallback:

1. Read the CSV file using Read tool
2. Expected columns:
   - Issue key - Ticket ID (e.g., SSP-3614)
   - Summary - Task name
   - Description - Detailed description
   - Custom field (Merge Request) - GitHub PR link

If CSV data does not include comments, explicitly mention that Jira comments could not be checked unless Jira MCP is available.

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
     - What caused the issue, for bug fixes and operational incidents
     - What was adjusted technically or behaviorally, not just that it was fixed
     - Why it changed (if relevant)
     - Impact on users/developers
   - Max 3-5 bullet points per task

Additional rules:
- Avoid vague one-line bug summaries such as "Fixed broken page" or "Fixed font loading". For fixes, include the cause and the concrete correction when the information is available from Jira, PR body, PR diff, commits, or comments.
- If a task has no PR, do not assume it is invalid. Warn the user and inspect Jira comments for context.
- If a no-PR task was completed as part of another task in the same sprint, merge it into the relevant main item.
- Prefer separate article items for unrelated tasks. Merge tasks only when they form one coherent feature/workstream, such as a larger GTM/dataLayer rollout.

### Step 6: Save Markdown

1. Save the markdown file
2. Keep the markdown article clean and copy-friendly
3. Do not automatically embed screenshots into the markdown

### Step 7: Offer Visual Attachments (Optional)

After the markdown is generated, check whether Playwright MCP/browser tools are available and whether the application is reachable.

**Required:** when Playwright MCP is available, generate screenshots for UX-relevant tickets as part of the workflow (do not wait for a separate prompt). The default target URL is the production environment - https://cz.ssfwcc.prod.shopsys.cloud/. If production demo data turn out to be non-standard (missing demo products, changed transports), switch to the Odin review environment of the current release branch (e.g. `https://cz.20-0.odin.shopsys.cloud`, SK domain `https://20-0.odin.shopsys.cloud/sk`, admin `https://20-0.odin.shopsys.cloud/admin/`).

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

When assets are generated, update the markdown item only with a short textual reference, not an embedded image. Every screenshot and video gets a Czech caption (what the asset shows, one short sentence); the same caption is used later in Confluence. Use this format:

```markdown
- Příloha: `sprint-summary-assets/SSP-3891-variant-parameters.png` (Výběr variant podle parametrů na detailu produktu)
- Příloha: `sprint-summary-assets/SSP-3891-variant-parameters.mp4` (video 30 s: Přepínání variant a aktualizace ceny)
```

This keeps the article easy to preview in IDEs and easy to copy to Confluence.

#### Screenshots with Playwright MCP

- `browser_take_screenshot` can save only inside the project (`.playwright-mcp/`) or the MCP `--output-dir`; save there and `mv` the file into the assets directory.
- Test data may be created on the review environment when the user allows it (orders with the user's e-mail, cancelled GoPay payment, temporarily disabling a transport on a domain). Restore every temporary change afterwards and list all created records (order numbers, approved reviews) in the final report.
- Scenarios that need an admin login: open the admin login page in the Playwright window and let the user log in; never type credentials. The admin session on Odin expires after a few minutes, so prepare the scenario (URLs, element names, script) before asking for the login and run everything immediately afterwards.
- Login badge "Naposledy použito" can be shown without credentials by setting `localStorage['shopsys-platform-persist-store-2'].state.lastLoginType` to `web` or `google` and reloading.

#### Video recording with Playwright MCP

Prerequisites (one-time, needs a session restart):
- Video recording works only with `@playwright/mcp@0.0.40` (newer versions dropped `--save-video`):
  ```
  claude mcp remove playwright
  claude mcp add playwright -- npx -y @playwright/mcp@0.0.40 --save-video=1440x900 --output-dir /Users/<user>/Downloads/playwright-videos
  ```
- `ffmpeg` must be installed (`brew install ffmpeg`).

Recording rules:
- One tab per scenario. The recording starts when the tab is created and the file is written only when the tab is closed (`browser_tabs close` or `browser_close`). After closing look for the newest `*.webm` both in `--output-dir` and in `$TMPDIR/playwright-mcp-output/`.
- The persistent profile records every open tab, including tabs the user opens in that window. Do not let the user browse in the Playwright window and delete all `*.webm` files once the clips are exported (recordings reach gigabytes).
- In 0.0.40 `browser_click` requires a `ref`; drive the whole scenario from one `browser_evaluate` async function instead (sleep helper, `scrollIntoView`, `element.click()`, `dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowRight', bubbles: true}))`). A full navigation destroys the evaluate context; continue in a second evaluate.
- The mouse cursor is not recorded, but every video must show one - viewers follow the cursor to see what is happening. Inject a fake cursor (SVG arrow, `position: fixed`, CSS transition ~0.7 s) at the start of every scenario so it is visible from the first frame until the end of the clip. Before every click move it to the element center, wait ~1 s, show a short click ripple, then click. Move it to the target element also before typing, key presses (e.g. reorder via ArrowLeft/ArrowRight) and hovers. See the snippet in "Playbook: scripted scenario with visible cursor".
- A full navigation removes the injected cursor. Store its last position (e.g. in `sessionStorage`) and re-inject it at that position as the first step of the next evaluate, so the cursor never disappears or jumps between pages.
- Pacing: wait 3-4 s after every action, keep the sticky header in mind when scrolling (`window.scrollTo({top: rowTop - 230})` instead of `scrollIntoView` so the clicked control stays visible), hold the final state 4-6 s, and avoid long static starts.
- Return timing marks (`performance.now()`) from the evaluate for orientation, but trust only the frames of the converted MP4.

Post-processing:
- WebM timestamps are unreliable for seeking. Convert first to a constant frame rate MP4 and cut from that file:
  ```
  ffmpeg -i rec.webm -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2,fps=25" -c:v libx264 -crf 23 -pix_fmt yuv420p -an full.mp4
  ffmpeg -i full.mp4 -ss 33.5 -to 63.5 -c:v libx264 -crf 23 -pix_fmt yuv420p -an -movflags +faststart SSP-XXXX-slug.mp4
  ```
- Remove dead segments with trim/concat instead of re-recording:
  ```
  ffmpeg -i in.mp4 -filter_complex "[0:v]trim=0:28.5,setpts=PTS-STARTPTS[a];[0:v]trim=41:48,setpts=PTS-STARTPTS[b];[a][b]concat=n=2:v=1:a=0[v]" -map "[v]" -c:v libx264 -crf 23 -pix_fmt yuv420p -an -movflags +faststart out.mp4
  ```
- Verify every clip with a contact sheet before publishing (including that the cursor is visible in every frame). Use exact frame indices (25 fps) rather than `fps=1/N` seeking, size the tile grid to the frame count, and give every sheet a new file name (the Read tool caches images by path):
  ```
  ffmpeg -i full.mp4 -vf "select='eq(n\,500)+eq(n\,850)+eq(n\,1100)',scale=440:-1,tile=3x1" -fps_mode vfr -frames:v 1 check.png
  ```
- Target 20-40 s per clip; the markdown reference must state the real duration and the caption what the clip shows.

Storefront selectors that proved stable:
- Compare buttons on product lists: `button[aria-label^="Přidat do porovnání produkt "]`; comparison page `/porovnani-produktu`; reorder handle `button[aria-label^="Změnit pořadí produktu"]` reacts to ArrowLeft/ArrowRight keydown; remove button `button[aria-label^="Odstranit z porovnání produkt "]`; undo toast button text `Vrátit zpět` lives ~2 s; differences checkbox `#comparison-only-differences`.
- Transport domain checkboxes in admin: `transport_form_basicInformation_enabled_{domainId}` (the header domain filter `admin_domains_form` is not the transport's domains).

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

When Confluence or visual attachments were involved, the final report must also list:
- every Confluence page version you created and what changed in it
- which attachments were uploaded and which placeholders still wait for a manual upload
- for every screenshot or video the user inserts manually (pending placeholders, extra media added later): instruct them to add the caption (list the Czech caption for each file) and turn on the border in the Confluence editor (select the image/video -> "Border" in the toolbar); videos recorded manually must show the mouse cursor (enable "show cursor" / click highlighting in the screen recorder)
- test data created or changed on the review environment (order numbers, approved reviews, temporarily disabled transports and whether they were restored)
- that the Playwright `*.webm` recordings were deleted after exporting the MP4 clips

### Step 9: Confluence Workflow

Always create the Confluence article as a published page that is open to everyone in the space (`isPrivate: false`) when Confluence MCP is available.

Place the page under the same parent/folder as the previous sprint summary so it inherits the space's default permissions (visible to everyone in the space).

After creating the page, give the user its link and tell them it is already published and visible to everyone in the space, so they can review and edit it directly.

If Confluence MCP is unavailable, instruct the user to create the article manually in Confluence here:

```
https://shopsys.atlassian.net/wiki/spaces/PRG/folder/2698510337?atlOrigin=eyJpIjoiMTIzN2EwNmQyYzMyNGFiY2I1OTU1YmVkMjk4YTk1MTciLCJwIjoiYyJ9
```

#### Attachments and embedded media

Confluence MCP cannot upload attachments, so:
1. Insert placeholders first: `<div data-type="panel-info"><p>Zde nahrát screenshot <code>FILENAME</code></p></div>` (use "GIF"/"video" for other types).
2. Upload the files through a browser that is logged in to Atlassian. The REST endpoint `POST /wiki/rest/api/content/{pageId}/child/attachment` rejects browser sessions with "XSRF check failed" even with `X-Atlassian-Token: no-check`; use the legacy form instead:
   - Playwright: `browser_navigate` to `https://shopsys.atlassian.net/wiki/pages/viewpageattachments.action?pageId={pageId}`, `browser_click` on "Upload file", `browser_file_upload` with the local path, `browser_click` on "Attach". One file per round trip. The Playwright persistent profile keeps the Atlassian login between runs.
   - Claude in Chrome: inject `<input type="file" id="claude-upload-input" multiple>`, use `file_upload` on its ref (max 10 MB per call), then POST each file from page JavaScript to `/wiki/pages/doattachfile.action?pageId={pageId}` with fields `atl_token` (read from the `viewpageattachments.action` HTML), `file_0`, `comment_0`, `confirm=Attach`. The `javascript_tool` is blocked for code containing query strings like `version=`/`status=historical`, so keep such requests to Playwright or the Atlassian MCP.
3. Read the media ids: `GET /wiki/rest/api/content/{pageId}/child/attachment?limit=100&expand=extensions` -> `extensions.fileId`.
4. Replace every placeholder with a bordered, captioned media node (works for PNG, GIF and MP4). The border is an ADF mark that the HTML format does not carry, so do this step in ADF: load the page with `getConfluencePage` `contentFormat: "adf"`, replace each placeholder panel with the node below, and write it back with `updateConfluencePage` `contentFormat: "adf"`:
   ```json
   {"type": "mediaSingle", "attrs": {"layout": "center", "width": 760, "widthType": "pixel"}, "content": [
     {"type": "media", "attrs": {"type": "file", "id": "{fileId}", "collection": "contentId-{pageId}", "alt": "{filename}", "width": 1440, "height": 900},
      "marks": [{"type": "border", "attrs": {"size": 2, "color": "#091e4224"}}]},
     {"type": "caption", "content": [{"type": "text", "text": "Výběr variant podle parametrů na detailu produktu"}]}
   ]}
   ```
   - Every screenshot and video must have both the border mark and a caption; the caption is the Czech description from the markdown reference (what the asset shows, without the file name).
   - Use the real pixel dimensions (`sips -g pixelWidth -g pixelHeight`, `ffprobe`), narrow widths (e.g. 390) for mobile screenshots.
5. Re-uploading a file with the same name creates a new attachment version; the page picks it up automatically (the media id in the body changes) and the page version does not change. Only the caption may need an update.

If attachment upload is not possible, keep the placeholders and clearly tell the user which local files need to be uploaded manually, together with the caption for each file and a reminder to turn on the border.

#### Safe page updates

`updateConfluencePage` replaces the whole body, so it silently overwrites edits the user made in the editor meanwhile.
- Before every update call `getConfluencePage` (the result is saved to a file when large; parse the JSON with Python), check the version number against the last version you wrote, and diff the body (strip `data-local-id="..."` attributes / `localId` first). Apply your change on top of the current body, never on your own last copy.
- Once the page contains media, read and write it only in ADF (`contentFormat: "adf"`). An HTML round trip drops the `border` marks of all images and videos (including borders the user added in the editor).
- If an overwrite already happened, restore the user's version via `https://shopsys.atlassian.net/wiki/pages/viewpreviousversions.action?pageId={pageId}` -> "Restore" (Playwright or Chrome), then reapply your change. A human-readable diff of two versions is at `.../wiki/pages/diffpagesbyversion.action?pageId={pageId}&selectedPageVersions=A&selectedPageVersions=B`.
- Mention every page version you created in the final report so the user can tell their edits from yours.

### Step 10: Distribution Instructions

Once the article is ready, always instruct the user with the following steps:

1. Verify the article contents (and optionally have a colleague review it).
   When adding or replacing screenshots and videos manually, give each one a caption describing what it shows and turn on its border (select the media -> "Border" in the toolbar), so all media in the article look the same. When recording videos manually, keep the mouse cursor visible (enable "show cursor" and ideally click highlighting in the screen recorder).
2. Once satisfied with the contents, create a public link for the Confluence page.
3. Send that public link to the marketing department so they distribute the summary by e-mail to the subscribers.

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
- Příloha: `sprint-summary-assets/SSP-3674-contact-form-state.png` (Vyčištěný kontaktní formulář po odeslání)

#### Admin: Chyba ve vyhledávání rozšířeného filtru
- **Jira:** [SSP-3748](https://shopsys.atlassian.net/browse/SSP-3748) | **PR:** [#4334](https://github.com/shopsys/shopsys/pull/4334)
- Opraveno dvojité volání requestu při použití našeptávání

---

## Výkon a bezpečnost

#### Storefront: Optimalizace broadcast channel
- **Jira:** [SSP-2013](https://shopsys.atlassian.net/browse/SSP-2013) | **PR:** [#4327](https://github.com/shopsys/shopsys/pull/4327)
- Při více otevřených záložkách se query na košík volá pouze jednou
```

## Playbook: scripted scenario with visible cursor

Run inside `browser_evaluate` (Playwright MCP 0.0.40). Adjust selectors and timings per scenario.

```js
async () => {
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));
  const cursor = document.createElement('div');
  cursor.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24"><path d="M4 2 L4 20 L8.5 15.5 L11.5 22 L14 21 L11 14.5 L17.5 14.5 Z" fill="#111" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/></svg>';
  cursor.style.cssText = 'position:fixed;left:700px;top:500px;z-index:2147483647;pointer-events:none;transition:left .7s ease,top .7s ease;filter:drop-shadow(0 1px 2px rgba(0,0,0,.5))';
  document.body.appendChild(cursor);
  const ripple = document.createElement('div');
  ripple.style.cssText = 'position:fixed;width:34px;height:34px;border-radius:50%;border:3px solid #2563eb;z-index:2147483646;pointer-events:none;opacity:0;transform:translate(-50%,-50%) scale(.4);transition:opacity .35s,transform .35s';
  document.body.appendChild(ripple);
  const moveTo = async (el) => {
    const r = el.getBoundingClientRect();
    const x = r.left + r.width / 2, y = r.top + r.height / 2;
    cursor.style.left = (x - 3) + 'px'; cursor.style.top = (y - 2) + 'px';
    await sleep(750);
    el.dispatchEvent(new MouseEvent('mouseover', {bubbles: true}));
    await sleep(1000);
    return {x, y};
  };
  const clickWithCursor = async (el) => {
    const {x, y} = await moveTo(el);
    ripple.style.left = x + 'px'; ripple.style.top = y + 'px';
    ripple.style.opacity = '1'; ripple.style.transform = 'translate(-50%,-50%) scale(1)';
    el.click();
    await sleep(350);
    ripple.style.opacity = '0'; ripple.style.transform = 'translate(-50%,-50%) scale(.4)';
  };
  await sleep(2500);
  await clickWithCursor(document.querySelector('button[aria-label^="Odstranit z porovnání produkt 47"]'));
  await sleep(700);
  const undo = [...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Vrátit zpět');
  if (undo) await clickWithCursor(undo);
  await sleep(4000);
  return 'done';
}
```

Typing into inputs: use the native value setter plus `input`/`change` events so React/Stimulus controllers react:
`Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set.call(input, text); input.dispatchEvent(new Event('input', {bubbles: true}))`.

## Audience

Target audience of the article:
- **Developers** - technical details, breaking changes, new APIs
- **Project managers** - overview of completed work, new features
- **Other colleagues** - high-level summary of changes

Write so that the article is understandable for non-technical colleagues, but also contains enough technical details for developers.
