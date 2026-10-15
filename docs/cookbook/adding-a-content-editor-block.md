# Adding a Content Editor Block

This cookbook adds a block to the [content editor](../administration/content-editor.md) in a project. `project-base` ships two such blocks as working examples: the web block `faq` (`FaqBlock`) and the mail block `mail-promo` (`MailPromoBlock`). The reference of blocks and fields is in [Content Editor Blocks](../extensibility/content-editor-blocks.md).

## 1. Create the block class and register it

Create the class in `src/ContentEditor/Block/`, see `FaqBlock.php`:

```php
#[ContentBlock(type: 'faq')]
final class FaqBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('FAQ item');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:help';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(TextField::class)::create('question')->label(t('Question'))->required(),
            ExtendedClassNameResolver::resolve(WysiwygField::class)::create('answer')->label(t('Answer'))->required(),
        ];
    }
}
```

Make the directory a service resource in `config/services.yaml`, the block is then tagged and found by the registry automatically:

```yaml
App\ContentEditor\Block\:
    resource: '../src/ContentEditor/Block/'
```

A block for mails adds `profiles: [ContentProfileEnum::MAIL]` to the attribute, see `MailPromoBlock.php`.

## 2. Translate the labels and import the icon

```bash
php phing translations-dump
php bin/console ux:icons:import tabler:help
```

Fill the new keys in `translations/messages.cs.po` and `messages.sk.po`. The icon is saved to `assets/icons/tabler/`, commit it.

## 3. Render the block on the storefront

Only for a block of the web profile. Describe the data in `types/contentEditor.ts`:

```ts
export type FaqBlockData = { question: string; answer: string };
```

Write the component, see `components/Blocks/ContentEditor/blocks/FaqBlock.tsx`, and add a `data-tid` to the `TIDs` enum in `cypress/tids.ts`:

```tsx
export const FaqBlock: FC<BlockProps<FaqBlockData>> = ({ block }) => (
    <details className="group rounded-xl border border-border-default p-4" data-tid={TIDs.content_block_faq}>
        <summary className="cursor-pointer font-bold">{block.data.question}</summary>
        <div className="user-text mt-2" dangerouslySetInnerHTML={{ __html: block.data.answer }} />
    </details>
);
```

The HTML of a `WysiwygField` is sanitized on the backend, so it can be rendered as it is. Register the component in `components/Blocks/ContentEditor/blockComponents.ts`:

```ts
faq: FaqBlock,
```

The preview in the administration uses the same map, so the block appears there without another change.

## 4. Write the mail template

Only for a block of the mail profile. Create `templates/bundles/ShopsysFrameworkBundle/ContentEditor/Mail/<type>.html.twig`, see `mail-promo.html.twig`. Use tables and inline styles, the template receives `data`, the result of `export()` of the block. A mail block without a template fails to render.

## 5. Add demo data

Add the block to a demo document. `ContentDocumentBuilder` (`src/DataFixtures/Demo/DemoDataFactory/`) has a method per block, add one for yours and call it in the fixture, e.g. `->faq($question, $answerHtml)` in `PressArticleDataFixture`. Translate the texts of the fixture with `translations-dump` as in step 2. Export the articles to Elasticsearch again after the fixtures are loaded, the storefront reads the exported document.

## 6. Test it

- `tests/App/Functional/Component/ContentEditor/ProjectBlocksTest.php`: the registry offers the block in its profile, a document with the block normalizes and exports, the mail renderer uses the template
- `storefront/vitest/components/Blocks/ContentEditor/FaqBlock.test.tsx`: the component renders the data

Run the tests and the checks of the project, `php phing standards-fix`, `php phing tests-functional-single -Dtest.name=ProjectBlocksTest` and `pnpm vitest run` in the storefront.
