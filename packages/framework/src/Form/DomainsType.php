<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DomainsType extends AbstractType
{
    public function __construct(private readonly Domain $domain)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $builder->add((string)$domainConfig->getId(), CheckboxType::class, [
                'required' => false,
                'label' => $domainConfig->getName(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
        ]);
    }
}
