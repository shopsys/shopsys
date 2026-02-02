<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

final class ColorPickerType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return TextType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $mandatoryConstraints = [
            new Regex([
                'pattern' => '/^#[a-fA-F0-9]{6}$/',
                'message' => 'Color must be a valid hexadecimal code (e.g., #3333ff)',
            ]),
        ];

        $resolver->setNormalizer(
            'constraints',
            function (Options $options, $constraints) use ($mandatoryConstraints) {
                return array_merge($mandatoryConstraints, $constraints);
            },
        );

        $resolver->setDefault('attr', ['data-coloris' => '']);
    }
}
