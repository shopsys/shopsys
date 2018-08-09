<?php

namespace Shopsys\FrameworkBundle\Form;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Component\Css\CssFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WysiwygTypeExtension extends AbstractTypeExtension
{
    const ALLOWED_FORMAT_TAGS = 'p;h2;h3;h4;h5;h6;pre;div;address';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Css\CssFacade
     */
    private $cssFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    public function __construct(CssFacade $cssFacade, Localization $localization)
    {
        $this->cssFacade = $cssFacade;
        $this->localization = $localization;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $cssVersion = $this->cssFacade->getCssVersion();

        $resolver->setDefaults([
            'config' => [
                'contentsCss' => [
                    'assets/admin/styles/wysiwyg_' . $cssVersion . '.css',
                ],
                'language' => $this->localization->getLocale(),
                'format_tags' => self::ALLOWED_FORMAT_TAGS,
            ],
        ]);
    }

    public function getExtendedType()
    {
        return CKEditorType::class;
    }
}
