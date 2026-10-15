# Content Editor Blocks

A project adds, replaces or removes blocks of the [content editor](../administration/content-editor.md) without touching the packages. A block is one class on the backend, one React component on the storefront (web profile) or one Twig template (mail profile).

- **add** a block: create a class with `#[ContentBlock(type: ...)]`
- **replace** a built-in block: create a class with the same `type` and a higher `priority`
- **remove** a block: list its type in `shopsys_framework.content_editor.disabled_types`

There are no extension classes, hooks or configuration objects.

## Adding a block

A block is a class extending `AbstractContentBlock` and marked with the `#[ContentBlock]` attribute. Every class with the attribute is tagged `shopsys.content_editor.block` and picked up by `ContentBlockRegistry`, so no manual tagging is needed.

!!! note "The class has to be a service"

    The `App\` resource in `services.yaml` of `project-base` registers only classes with certain suffixes (`Factory`, `Provider`, ...), a block is not among them. Register the directory of your blocks explicitly, `autoconfigure` is enabled by `_defaults`:

    ```yaml
    App\ContentEditor\Block\:
        resource: '../src/ContentEditor/Block/'
    ```

The attribute has three arguments:

| Argument   | Default | Description                                                                             |
| ---------- | ------- | --------------------------------------------------------------------------------------- |
| `type`     | -       | Identifier stored in the document, e.g. `heading`; never change it once content exists  |
| `profiles` | `web`   | Profiles the block is offered in: `ContentProfileEnum::WEB`, `ContentProfileEnum::MAIL` |
| `priority` | `0`     | The block with the highest priority wins when more blocks share a `type`                |

The block implements these methods (`AbstractContentBlock` gives defaults for the rest):

| Method             | Description                                                                                   |
| ------------------ | --------------------------------------------------------------------------------------------- |
| `getLabel()`       | Name in the picker, wrap it in `t()` so it is translated                                      |
| `getIcon()`        | Icon name, `null` for none (default)                                                          |
| `getCategory()`    | `AbstractContentBlock::CATEGORY_CONTENT` (default) or `CATEGORY_LAYOUT`; groups the picker    |
| `getFields()`      | Fields of the block, the form of the block is generated from them                             |
| `export()`         | Converts stored data to what the storefront receives, returns the data unchanged by default   |
| `extractText()`    | Text of the block for the search and variable checks, joins the text of the fields by default |
| `getFormType()`    | Form type that replaces the generated form, `null` (default) keeps the generated one          |
| `getDefaultData()` | Data of a new block, built from the defaults of the fields by default                         |

Icons are Tabler icons in the form `tabler:name`, rendered by `ux_icon()`. A new icon has to be locked, see [Icons](../administration/icons.md). After adding labels run `php phing translations-dump`.

### Example

This is the built-in `heading` block, as a project block:

```php
<?php

declare(strict_types=1);

namespace App\ContentEditor\Block;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;

#[ContentBlock(type: 'heading')]
final class HeadingBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('Heading');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:heading';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            TextField::create('text')->label(t('Heading'))->required(),
            ChoiceField::create('level', ['h2', 'h3', 'h4'])->label(t('Level'))->default('h2'),
        ];
    }
}
```

The built-in blocks create fields through `ExtendedClassNameResolver::resolve(TextField::class)::create(...)`, so a project can replace a field class the usual way. In project code the direct call is enough.

The storefront component of the block, `components/Blocks/ContentEditor/blocks/HeadingBlock.tsx`:

```tsx
import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { HeadingBlockData } from 'types/contentEditor';

export const HeadingBlock: FC<BlockProps<HeadingBlockData>> = ({ block }) => {
    const Tag = block.data.level;

    return (
        <section className="user-text" data-tid={TIDs.content_block_heading}>
            <Tag>{block.data.text}</Tag>
        </section>
    );
};
```

## Fields

A field describes one value of a block: its name, label, default, constraints and the form type that edits it. The fields are created with `create('name')` and configured fluently:

```php
UrlField::create('url')->label(t('Link'))->help(t('Absolute or relative address'))->required()
```

| Field           | Value                                     | Form type         | Notes                                                      |
| --------------- | ----------------------------------------- | ----------------- | ---------------------------------------------------------- |
| `TextField`     | string                                    | `TextType`        |                                                            |
| `TextareaField` | string                                    | `TextareaType`    |                                                            |
| `WysiwygField`  | sanitized HTML                            | `WysiwygType`     | `variables([...])` offers placeholders                     |
| `NumberField`   | int or float                              | `IntegerType`     |                                                            |
| `CheckboxField` | bool                                      | `CheckboxType`    |                                                            |
| `ChoiceField`   | one of the given choices                  | `ChoiceType`      | `create('name', ['a', 'b'])`                               |
| `ColorField`    | hex color `#rgb` or `#rrggbb`             | `ColorPickerType` |                                                            |
| `UrlField`      | http(s), mailto, tel, `#` or relative URL | `UrlType`         | `allowVariables()` accepts a leading `{placeholder}`       |
| `MediaField`    | ID of a `Media`                           | `MediaType`       | Image from the media library                               |
| `ProductsField` | list of product IDs                       | `ProductIdsType`  | Sortable product picker                                    |
| `VariableField` | `{placeholder}` of an image variable      | `ChoiceType`      | Mail only, choices come from the `variables` of the editor |

Required values and rules:

- `->required()` adds `NotBlank`, other rules go to `->constraints([new Range(min: 100, max: 2000)])`
- the constraints run when the editor is finished ("Done") and again when the entity is saved, by `ContentDocumentValid`, which `ContentEditorType` adds automatically
- values are normalized on every save with `normalizeValue()` of the field: a value of a wrong shape becomes the default, a key that is not a field of the block is dropped

### Custom field widget

To edit a value with your own widget, extend `AbstractField` (or implement `FieldInterface`) and implement:

- `getFormType()` - class of the Symfony form type, which is rendered inside the block card
- `getFormOptions()` - options of that type; the parent returns `label`, `help`, `required` and `constraints`
- `normalizeValue()` - coerces any stored or submitted value to the shape of the field, falling back to the default
- `extractText()` - text of the value for the search, an empty string by default

Use the field in `getFields()` of a block. A block that needs a form unrelated to fields returns a form type from `getFormType()`, the editor then renders it in place of the generated form.

## Replacing a built-in block

Create a class with the type of the block and a higher `priority`. Extending the built-in class keeps everything you do not change. The attribute is not inherited, so repeat it with all arguments, including `profiles` for a mail block:

```php
#[ContentBlock(type: 'text', priority: 10)]
final class TextBlock extends \Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\TextBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('Paragraph');
    }
}
```

Two blocks with the same `type` and the same `priority` throw `ContentBlockTypeConflictException` when the registry is built.

## Removing a block

List the type in `disabled_types`:

```yaml
shopsys_framework:
    content_editor:
        disabled_types: [map]
```

The block disappears from the picker. Existing blocks of that type are dropped when the content is saved again, and skipped immediately in the storefront export and in mails.

## Storefront

The storefront renders a document with `ContentRenderer` (`components/Blocks/ContentEditor/ContentRenderer.tsx`). The renderer looks up the component of every block in the `blockComponents` map (`components/Blocks/ContentEditor/blockComponents.ts`), which is project code. A type without a component is not rendered, in development a warning is logged.

To add a block, add one entry to the map and write its component:

```ts
export const blockComponents: Record<string, FC<BlockProps<any>>> = {
    heading: HeadingBlock,
    // ...
    faq: FaqBlock,
};
```

The component receives `block`, the block as exported by the backend:

```ts
type TypeContentBlock<TData> = {
    id: string;
    type: string;
    data: TData; // the result of export() of the block
    children?: TypeContentBlock[][]; // slots of a container, each a list of blocks
};
```

`data` is produced when the document is exported to Elasticsearch, so after changing `export()` of a block, export the article indexes again. Describe `data` in `types/contentEditor.ts`, e.g. `HeadingBlockData`. The GraphQL field `content` of `Article` and `BlogArticle` is the whole document: `{ version, profile, blocks }`.

A container renders its slots with `ContentRenderer` again, see `ColumnsBlock.tsx`:

```tsx
{
    (block.children ?? []).map((slotBlocks, slotIndex) => (
        <ContentRenderer
            key={slotIndex}
            document={{ blocks: slotBlocks }}
            areProductsFetching={areProductsFetching}
            products={products}
        />
    ));
}
```

The preview of the editor uses the same `ContentRenderer` (page `content-editor-preview`), so a new block appears in it without any other change.

## Mail

Mail blocks (`profiles: [ContentProfileEnum::MAIL]`) are rendered on the server by `MailContentRenderer` with the Twig template `@ShopsysFramework/ContentEditor/Mail/<type>.html.twig`. A project adds or overrides a template in `templates/bundles/ShopsysFrameworkBundle/ContentEditor/Mail/<type>.html.twig`.

The template receives `data`, the result of `export()`, and `children`, the already rendered HTML of every slot. Mail clients support only tables and inline styles, so write the markup that way. Every block of the mail profile needs a template, otherwise rendering fails.

```twig
<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="50%" valign="top" style="padding:0 10px 0 0;">{{ children[0]|default('')|raw }}</td>
        <td width="50%" valign="top" style="padding:0 0 0 10px;">{{ children[1]|default('')|raw }}</td>
    </tr>
</table>
```

## Containers

A container block holds other blocks in its slots. It extends `AbstractColumnsBlock` (or implements `ContainerContentBlockInterface` with `getSlotCount()`), which has no fields. The layout is given by the type, so every layout is a separate block (`columns-1-1`, `columns-1-2`, ...) with a fixed number of slots and there is no action to change it:

```php
#[ContentBlock(type: 'columns-1-2')]
class ColumnsThirdTwoThirdsBlock extends AbstractColumnsBlock
{
    protected const string LAYOUT = '1/3-2/3';
    // getLabel() and getIcon() omitted
}
```

Containers cannot be nested: the picker does not offer them inside a slot and the normalizer moves the blocks of a container found in a slot into the parent slot.

## Validation

- `ContentDocumentValid` runs the constraints of every field, `ContentEditorType` adds it itself
- `ContentDocumentNotEmpty` fails for a document without blocks, add it to the `constraints` of the form field; its `message` and `groups` are configurable
- `ContentDocumentContainsVariable(variable: '{order_number}')` fails when the document does not use the placeholder in a text, link or variable field, used by mail templates with required variables

Validation of a single value belongs to the field (`required()`, `constraints()`), not to the form of the entity.

## Which classes are final

The classes that form the engine are `final`: `ContentBlockRegistry`, `ContentDocumentNormalizer`, `SlotFitter`, `ContentDocumentExporter`, `MailContentRenderer`, `GrapesJsHtmlConverter`, `MigrateGrapesJsContentTask` and the `ContentBlock` attribute in the framework; `ContentEditorType`, `ContentEditorComponent`, `BlockFormBuilder`, `ContentDocumentType`, `ContentBlockFormType`, `MediaPickerComponent`, `MediaType`, `WysiwygType` and `ProductIdsType` in the administration.

They are `final` because the guarantees of the editor depend on them: the registry resolves priorities and reports conflicts, the normalizer sanitizes HTML and fits blocks into slots, the exporter and the renderer skip blocks of types that no longer exist. Overriding any of them would silently break these guarantees for every block, so extend the editor through the points above instead. Form types are `final` by the rule of the repository and the Live Components carry the whole flow of the editor.

Blocks and fields are intentionally not `final`, so you can extend a built-in one. Your own project classes are `final` as everywhere in `project-base`.
