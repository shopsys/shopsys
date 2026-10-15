# Content Editor

The content editor edits the content of articles, blog articles and mail templates as a list of blocks (text, image, columns, ...). It replaces the former GrapesJS editor.

The content is stored as a JSON document (`ContentDocument`) in the `content` column of the entity, never as HTML. The storefront and the mail renderer turn the document into the final output, so a project can change the look of a block without touching the stored content.

A document has a `profile`, which decides what blocks it can contain:

- `web` - articles and blog articles, rendered by the storefront
- `mail` - mail templates, rendered to table-based HTML with inline styles

Blocks are added with a button and moved with the up and down buttons, there is no drag and drop. A block of a container type (columns) holds other blocks in its slots, containers cannot be nested.

To add your own blocks, see [Content Editor Blocks](../extensibility/content-editor-blocks.md).

## Using the editor in a form

Add `Shopsys\AdministrationBundle\Form\Admin\Type\ContentEditorType` to the form and map it to a `ContentDocument` property of the data object:

```php
$builder->add('content', ContentEditorType::class, [
    'profile' => ContentProfileEnum::WEB,
    'domain_id' => $domainId,
    'constraints' => [new ContentDocumentNotEmpty()],
]);
```

| Option          | Default | Description                                                                                                                                 |
| --------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `profile`       | `web`   | Profile of the document, see `ContentProfileEnum`                                                                                           |
| `allowed_types` | `null`  | Types of blocks the editor offers, `null` offers all blocks of the profile                                                                  |
| `variables`     | `[]`    | Placeholders offered in the rich text and used by variable fields (mail), each with `label`, `placeholder`, `isRequired` and `isImage`      |
| `domain_id`     | `null`  | Domain the content is shown on, used for media URLs in the preview; `null` takes the first domain with the locale of the edited translation |

The type is already used by the forms of articles (`ArticleFormType`), blog articles (`BlogArticleFormType`) and mail templates (`MailTemplateFormType`). The form value is carried as JSON in a hidden input, the document is normalized on submit: unknown fields are dropped, values are coerced to the shape of their field and HTML is sanitized.

## What the administrator sees

The form shows an "Edit content" button. The button opens the editor as a full-screen overlay:

- on the left is the live preview, on the right the blocks as cards with their settings
- "Add block" opens a picker with a filter, blocks are grouped to content and layout
- columns show their slots as collapsible sections, each with its own list of blocks
- "Done" validates all blocks on the server and writes the document into the form of the entity; while any block is invalid, the overlay stays open and shows the errors
- "Close" discards the changes after a confirmation

Nothing is saved until the form of the entity is saved. The preview of web content is the storefront in an iframe, with a switch between the desktop and the mobile width. The preview of mail content is the whole mail rendered on the server, the variables are shown as their labels.

## Blocks

### Web profile

| Type            | Fields                                                                                                    | Notes                                                                                       |
| --------------- | --------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- |
| `text`          | `html` (required)                                                                                         | Rich text, see [Rich text](#rich-text)                                                      |
| `heading`       | `text` (required), `level` (`h2`, `h3`, `h4`)                                                             | Plain text heading                                                                          |
| `image`         | `mediaId`, `alt`, `position` (`left`, `center`, `right`), `link`                                          | Image from the [media library](#media-library); the export adds `url`, `width` and `height` |
| `button-link`   | `text`, `url` (both required), `openInNewTab`, `position`, `variant` (`primary`, `secondary`, `tertiary`) | Rendered with the button styles of the storefront                                           |
| `video`         | `url` (required)                                                                                          | YouTube or Vimeo link; the export adds `provider`, `videoId` and `embedUrl`                 |
| `map`           | `url` (required)                                                                                          | Google Maps embed link                                                                      |
| `iframe`        | `url` (required), `height` (100-2000 px, default 400)                                                     | The host of the URL has to be in `embed_hosts`                                              |
| `products`      | `productIds` (at least one)                                                                               | Chosen with the product picker; the export adds `catnums` and skips deleted products        |
| `columns-1-1`   | -                                                                                                         | Two columns, 1/2 + 1/2                                                                      |
| `columns-1-2`   | -                                                                                                         | Two columns, 1/3 + 2/3                                                                      |
| `columns-2-1`   | -                                                                                                         | Two columns, 2/3 + 1/3                                                                      |
| `columns-1-1-1` | -                                                                                                         | Three columns, 1/3 each                                                                     |

URL fields accept `http(s)://`, `mailto:`, `tel:`, `#` and relative paths starting with a single `/`.

### Mail profile

| Type                  | Fields                                                                 | Notes                                                                                                                                         |
| --------------------- | ---------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `mail-text`           | `html` (required)                                                      | Rich text with the insertion of variables                                                                                                     |
| `mail-image`          | `mediaId`, `alt`, `position`, `link`                                   | `link` may start with a variable; the image is limited to 600 px width, mail clients do not scale it                                          |
| `mail-image-variable` | `variable` (required), `alt`                                           | Image whose address is a placeholder filled in when the mail is sent (e.g. a product image); offered only in templates with an image variable |
| `mail-button`         | `text`, `url` (both required), `position`, `color` (default `#00C8B7`) | `url` may start with a variable                                                                                                               |
| `mail-columns`        | -                                                                      | Two columns, 1/2 + 1/2                                                                                                                        |
| `mail-divider`        | -                                                                      | Horizontal line                                                                                                                               |

## Rich text

The `text` and `mail-text` blocks use `WysiwygType`, a rich text editor built on Tiptap that holds sanitized HTML. The toolbar offers:

- bold, italic, underline, strikethrough
- headings 2, 3 and 4
- bulleted and numbered list, quote, horizontal line
- link with address, title and "Open in a new tab"
- table with insert (3 x 3 with a header row), add and delete row, add and delete column, delete table
- clear formatting
- HTML source, which is parsed by the editor again, so markup it does not support is dropped
- insertion of a variable, only when the editor has `variables` (mail)

Images are not part of the text, use the image block.

### Sanitizer

Every rich text is sanitized on the server by `ContentHtmlSanitizer`, regardless of what the browser sends. The result is the only HTML that is stored.

- allowed elements: `p`, `br`, `strong`, `b`, `em`, `i`, `u`, `s`, `h2`, `h3`, `h4`, `ul`, `ol`, `li`, `a`, `blockquote`, `hr`, `table`, `thead`, `tbody`, `tr`, `td`, `th`, `span`
- allowed attributes: `href`, `title`, `target` and `rel` on `a`, `class` on `span`; relative links are allowed
- `block_elements` (`ContentHtmlSanitizer::BLOCKED_ELEMENTS`, e.g. `div`, `section`, `h1`, `pre`, `font`) are removed but their text is kept, so pasted content does not lose words
- every other element (e.g. `script`, `img`, `iframe`) is removed together with its content
- `rel="noopener"` is added to links with `target="_blank"`
- `max_input_length` is `-1`, otherwise Symfony silently truncates the input at 20000 bytes

The sanitizer is the Symfony HTML sanitizer `content_editor`, which the bundle prepends to `framework.html_sanitizer.sanitizers`. To change the allowed markup, configure it in `config/packages/framework.yaml`, your configuration is merged over the one of the bundle:

```yaml
framework:
    html_sanitizer:
        sanitizers:
            content_editor:
                allow_elements:
                    sub: []
                    sup: []
```

The toolbar does not change with the sanitizer. An element the toolbar cannot produce also needs an extension in the Tiptap editor (`packages/administration/assets/src/js/wysiwyg/extensions.js`), otherwise Tiptap drops it when the content is loaded.

## Configuration

The bundle keys are under `shopsys_framework.content_editor`:

```yaml
shopsys_framework:
    content_editor:
        disabled_types: [map]
        embed_hosts: [www.youtube.com, player.vimeo.com]
```

- `disabled_types` - types of blocks that are not registered; they disappear from the picker and are skipped when stored content is rendered or exported (default: none)
- `embed_hosts` - hosts the `iframe` block accepts (default: YouTube, Vimeo and Google Maps hosts); the list replaces the default one

## Preview in the storefront

The preview of web content is the page `/content-editor-preview` of the storefront, loaded in an iframe. The administration sends it the exported document with `postMessage`, the page renders it with the same `ContentRenderer` as articles.

The page accepts messages from the origin of the storefront only. When the administration is served from a different host, list its origin in the storefront environment variable `CONTENT_EDITOR_PREVIEW_ORIGINS` (comma-separated):

```
CONTENT_EDITOR_PREVIEW_ORIGINS=https://admin.example.com
```

The iframe address is the fixed relative path `/content-editor-preview` of the administration host, the web server has to pass it to the storefront (the default `nginx.conf` does). The header, footer and styles of the preview come from the storefront of that host, i.e. the first domain. Media URLs and products in the document are exported for the domain of the edited entity.

## Media library

Images come from the media library, a list of images under CMS > Media (`MediaController`, entity `Media`). The `image` and `mail-image` blocks hold only the `mediaId`, the editor offers a picker with upload (`MediaType` and `MediaPickerComponent`).

- access is controlled by the role `ROLE_MEDIA`
- administrators who can edit articles, blog articles or mail templates get the media rights `view` and `create` implicitly, so they can use the picker without a new role (`AdminRoleHierarchyProvider`)
- an image is stored with the `Image` pipeline, like the images of other entities, so resizing, imgproxy and the [CDN](../introduction/cdn.md) work the same way; mail images are resized to 600 px
- when the media of a block no longer exists, the block stays in the content and the storefront does not render the image

The files uploaded through elFinder (CKEditor fields such as product descriptions) are not part of the media library.

## Rendering

Web content: GraphQL fields `Article.content` and `BlogArticle.content` return the exported document as JSON. The export (`ContentDocumentExporter`) is written to the Elasticsearch indexes of articles and blog articles, so a change of the export of a block shows up after the indexes are exported again (`php bin/console shopsys:elasticsearch:data-export`). The export turns the stored data into the shape the storefront needs (media URLs, product catalog numbers, video embeds) and is never written back to the database. The storefront renders it with `ContentRenderer`, which maps each block type to a React component, see [the storefront side](../extensibility/content-editor-blocks.md#storefront).

Mail content: `MailContentRenderer` renders every block with the Twig template `@ShopsysFramework/ContentEditor/Mail/<type>.html.twig`. `MailTemplateFacade::getTemplateWithRenderedContent()` returns the template with the rendered content, which the mail facades pass to the mailer.

The text of the blocks (`extractText()`) is used for the search in articles and for the check of required variables in mail templates.

## Migration of the GrapesJS content

The post-deploy task `migrate_grapesjs_content` (`MigrateGrapesJsContentTask`) converts the HTML saved by GrapesJS to documents. It is a one-time task, see [Post-Deploy Tasks](../introduction/post-deploy-tasks.md).

- it converts articles, blog article translations and mail templates that have no `content` yet, from the `legacy_grapesjs_*` columns; the legacy columns are not changed
- images from `content/` are imported to the media library, images from elsewhere keep their original URL (the `image` block then holds `url` instead of `mediaId`)
- the task is safe to run again, a row that cannot be converted is skipped and reported
- what could not be converted exactly (products with unknown catalog numbers, unsupported markup) is printed as a table at the end of the task and logged as a warning with the context key `content_editor_migration`; check the converted mail templates and save the ones from the table again
- converted articles and blog articles are exported to Elasticsearch again

Run it manually with `php bin/console deploy:post-deploy:run-tasks`.
