<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WysiwygTypeExtension extends AbstractTypeExtension
{
    protected const ALLOWED_FORMAT_TAGS = 'p;h2;h3;h4;h5;h6;pre;div;address';

    protected const ADMIN_WYSIWYG_ENTRY = 'admin-wysiwyg';

    public function __construct(
        private readonly Localization $localization,
        private readonly string $entrypointsPath,
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

    private function getContentCss(): array
    {
        $entrypointsOutput = [];
        $entrypointsJsonContent = file_get_contents($this->entrypointsPath);
        $entrypointsArrayContent = json_decode($entrypointsJsonContent, true);
        $entrypoints = $entrypointsArrayContent['entrypoints'];

        if (array_key_exists(static::ADMIN_WYSIWYG_ENTRY, $entrypoints) === true) {
            $entrypointsOutput = array_merge($entrypointsOutput, $entrypoints[static::ADMIN_WYSIWYG_ENTRY]['css']);
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
