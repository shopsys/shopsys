<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Reprise\Asset\EntrypointsLookupInterface;

final class WysiwygTypeExtension extends AbstractTypeExtension
{
    protected const ALLOWED_FORMAT_TAGS = 'p;h2;h3;h4;h5;h6;pre;div;address';

    protected const ADMIN_WYSIWYG_ENTRY = 'admin-wysiwyg';

    public function __construct(
        private readonly Localization $localization,
        private readonly EntrypointsLookupInterface $entrypointsLookup,
        private readonly Packages $packages,
        private readonly WysiwygCdnDataTransformer $wysiwygCdnDataTransformer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'config' => [
                'contentsCss' => $this->getContentCss(),
                'language' => $this->localization->getRequestLocale(),
                'format_tags' => static::ALLOWED_FORMAT_TAGS,
            ],
            'available_variables' => [],
            'contains_html' => true,
        ]);

        $resolver->setAllowedTypes('available_variables', 'array');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->wysiwygCdnDataTransformer);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (count($options['available_variables']) > 0) {
            $variablesHtml = $this->buildVariablesHelpHtml($options['available_variables']);

            if (array_key_exists('help', $view->vars) && $view->vars['help'] !== null) {
                $view->vars['help'] .= $variablesHtml;
            } else {
                $view->vars['help'] = $variablesHtml;
            }

            $view->vars['help_html'] = true;
        }
    }

    /**
     * @param array<string, string> $variables
     */
    private function buildVariablesHelpHtml(array $variables): string
    {
        $items = [];

        foreach ($variables as $variable => $description) {
            $items[] = sprintf(
                '<li><code>%s</code> &ndash; %s</li>',
                htmlspecialchars($variable, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
            );
        }

        return sprintf(
            '<div><h5>%s</h5><ul class="list-unstyled">%s</ul></div>',
            t('Available placeholders'),
            implode('', $items),
        );
    }

    /**
     * @return string[]
     */
    private function getContentCss(): array
    {
        $entrypointsOutput = [];

        // the lookup deduplicates returned files per request, so the entry must not share CSS chunks
        // with the entries rendered by the layout, otherwise those chunks would be missing from the page
        if ($this->entrypointsLookup->entryExists(static::ADMIN_WYSIWYG_ENTRY)) {
            // entrypoints.json holds web-root relative references, CKEditor needs URLs
            foreach ($this->entrypointsLookup->getCssFiles(static::ADMIN_WYSIWYG_ENTRY) as $cssFile) {
                $entrypointsOutput[] = $this->packages->getUrl($cssFile);
            }
        }

        $entrypointsOutput[] = '/tailwind-for-admin/style.css';

        return $entrypointsOutput;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield CKEditorType::class;
    }
}
