<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Shopsys\FrameworkBundle\Model\Media\Exception\MediaNotFoundException;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Holds the ID of a media from the library, the media is chosen in the MediaPickerComponent.
 * The picker component has to be rendered on the page of the form.
 */
final class MediaType extends AbstractType
{
    public function __construct(
        private readonly MediaFacade $mediaFacade,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['legacy_url' => null])
            ->setAllowedTypes('legacy_url', ['string', 'null']);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['media'] = $this->findMedia($form->getData());
        $view->vars['legacy_url'] = $options['legacy_url'];
    }

    #[Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }

    private function findMedia(mixed $mediaId): ?Media
    {
        if (!is_numeric($mediaId)) {
            return null;
        }

        try {
            return $this->mediaFacade->getById((int)$mediaId);
        } catch (MediaNotFoundException) {
            // the media was deleted after the form value was stored
            return null;
        }
    }
}
