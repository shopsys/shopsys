# Coding standards

## Eslint

It can show you errors on demand when writing your code - you have to install an editor plugin to use it. It can be used on the server-side in any test.

Rules are defined in files `.eslintignore` and `eslint.config.mjs`.

### HTML Validation Rules

ESLint is configured with custom rules to catch HTML validation issues and ensure proper semantic markup. These rules help maintain accessibility and valid HTML structure.

#### Commands

```bash
# Check for validation issues
docker compose exec storefront pnpm lint

# Fix issues automatically (where possible)
docker compose exec storefront pnpm check--fix

# Check specific file
docker compose exec storefront npx eslint path/to/component.tsx
```

#### Button Content Validation

Buttons can only contain **phrasing content** elements, not block-level elements.

**❌ Invalid Examples:**

```tsx
// Button containing div - INVALID
<button>
    <div>Click me</div>
</button>

// Button containing paragraph - INVALID
<button>
    <p>Submit form</p>
</button>

// Button containing heading - INVALID
<button>
    <h3>Action Button</h3>
</button>

// Button containing list - INVALID
<button>
    <ul>
        <li>Option 1</li>
    </ul>
</button>

// Button containing sectioning elements - INVALID
<button>
    <section>Content</section>
</button>

<button>
    <article>Content</article>
</button>
```

**✅ Valid Examples:**

```tsx
// Button with text only - VALID
<button>Simple text button</button>

// Button containing span - VALID
<button>
    <span>Text inside span</span>
</button>

// Button containing phrasing elements - VALID
<button>
    <strong>Bold text</strong>
</button>

<button>
    <em>Emphasized text</em>
</button>

<button>
    <small>Small text</small>
</button>

<button>
    <code>Code text</code>
</button>

// Button containing img/svg - VALID
<button>
    <img alt="Icon" src="/icon.svg" />
    Button with icon
</button>

<button>
    <svg aria-hidden="true" viewBox="0 0 24 24">
        <path d="..." />
    </svg>
    Button with SVG icon
</button>

// Button with multiple phrasing elements - VALID
<button>
    <span>Click</span>
    <strong>here</strong>
    <em>now</em>
</button>

// Button with nested phrasing elements - VALID
<button>
    <span>
        <strong>Bold text in span</strong>
    </span>
</button>
```

#### Interactive Element Nesting

Interactive elements cannot be nested inside other interactive elements.

**❌ Invalid Examples:**

```tsx
// Button inside anchor - INVALID
<a href="/page">
    <button>Click me</button>
</a>

// Anchor inside button - INVALID
<button>
    <a href="/page">Link</a>
</button>

// Button inside button - INVALID
<button>
    <button>Nested button</button>
</button>
```

**✅ Valid Alternatives:**

```tsx
// Use role="button" for complex interactive content - VALID
<div
    role="button"
    tabIndex={0}
    onClick={handleClick}
    onKeyDown={handleKeyDown}
>
    <div>Complex content structure</div>
    <p>With multiple elements</p>
</div>

// Safari compatibility: Anchors with role="button" need explicit tabIndex
<a
    href="/page"
    role="button"
    tabIndex={0}  // ← Required for Safari keyboard accessibility
    onClick={handleClick}
>
    <span>Button-like link</span>
</a>

// Separate interactive elements - VALID
<div>
    <a href="/page">Go to page</a>
    <button onClick={handleAction}>Perform action</button>
</div>

// Use appropriate semantic elements - VALID
<a href="/page" className="button-style">
    Link styled as button
</a>

<button onClick={handleClick}>
    <span>Button with proper content</span>
</button>
```

#### HTML Phrasing vs Flow Content

**Phrasing Content (Valid in buttons):**
`a`, `abbr`, `area`, `audio`, `b`, `bdi`, `bdo`, `br`, `button`, `canvas`, `cite`, `code`, `data`, `datalist`, `del`, `dfn`, `em`, `embed`, `i`, `iframe`, `img`, `input`, `ins`, `kbd`, `label`, `mark`, `math`, `meter`, `noscript`, `object`, `output`, `picture`, `progress`, `q`, `ruby`, `s`, `samp`, `script`, `select`, `small`, `span`, `strong`, `sub`, `sup`, `svg`, `template`, `textarea`, `time`, `u`, `var`, `video`, `wbr`

**Flow Content (Invalid in buttons):**
`div`, `p`, `h1-h6`, `ul`, `ol`, `li`, `section`, `article`, `aside`, `header`, `footer`, `nav`, `main`, `address`, `blockquote`, `details`, `dialog`, `fieldset`, `figure`, `form`, `table`, etc.

#### Automatic Detection

Your ESLint setup automatically catches:

✅ **`jsx-a11y/interactive-supports-focus`** - Elements with `role="button"` that need `tabIndex`  
✅ **`jsx-a11y/click-events-have-key-events`** - Click handlers without keyboard support  
✅ **`jsx-a11y/no-static-element-interactions`** - Non-interactive elements with event handlers

**Special Case - Safari Anchors:**
Anchors with `href` and `role="button"` are not flagged by ESLint (they're normally focusable), but Safari requires explicit `tabIndex={0}` for proper keyboard access.

#### Why These Rules Matter

1. **HTML Validation:** Ensures your markup follows HTML5 standards
2. **Accessibility:** Screen readers expect semantic HTML structure
3. **Browser Compatibility:** Prevents unexpected behavior across browsers (especially Safari)
4. **SEO:** Search engines rely on proper HTML semantics
5. **Maintainability:** Consistent structure makes code easier to understand

## Prettier

You can format your code on save or be fired by a key shortcut - you have to install an editor plugin to use it.

Rules are defined in files `.prettierignore` and `prettier.config.js`.

## Editorconfig

Adds coding standards into your IDE even if you don't have any plugins installed.

Rules are defined in file `.editorconfig`.
