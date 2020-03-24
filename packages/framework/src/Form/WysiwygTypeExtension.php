<?php

namespace Shopsys\FrameworkBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WysiwygTypeExtension extends AbstractTypeExtension
{
    protected const ALLOWED_FORMAT_TAGS = 'p;h2;h3;h4;h5;h6;pre;div;address';

    protected const KEY_LESS_ADMIN = 'admin-wysiwyg';

    protected const KEY_LESS_FRONTEND_PREFIX = 'frontend-wysiwyg-';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var string
     */
    private $entrypointsPath;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param string $entrypointsPath
     */
    public function __construct(Domain $domain, Localization $localization, string $entrypointsPath)
    {
        $this->domain = $domain;
        $this->localization = $localization;
        $this->entrypointsPath = $entrypointsPath;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'config' => [
                'contentsCss' => $this->getContentCss(),
                'language' => $this->localization->getLocale(),
                'format_tags' => static::ALLOWED_FORMAT_TAGS,
            ],
        ]);
    }

    /**
     * @return array
     */
    private function getContentCss(): array
    {
        $entrypointsOutput = [];
        $entrypoints = json_decode(file_get_contents($this->entrypointsPath), true)['entrypoints'];

        if (array_key_exists(static::KEY_LESS_ADMIN, $entrypoints) === true) {
            $entrypointsOutput = array_merge($entrypointsOutput, $entrypoints[static::KEY_LESS_ADMIN]['css']);
        }

        $keyOfFrontendWysiwygLess = static::KEY_LESS_FRONTEND_PREFIX . $this->domain->getId();
        if (array_key_exists($keyOfFrontendWysiwygLess, $entrypoints) === true) {
            $entrypointsOutput = array_merge($entrypointsOutput, $entrypoints[$keyOfFrontendWysiwygLess]['css']);
        }

        return $entrypointsOutput;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CKEditorType::class;
    }
}
