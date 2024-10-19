<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Administrator;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminDomainsFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $domainsGroup = $builder->create('domains', FormType::class, [
            'label' => false,
            'inherit_data' => true,
        ]);

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainsGroup->add((string)$domainConfig->getId(), CheckboxType::class, [
                'label' => $domainConfig->getName(),
                'required' => false,
            ]);
        }

        $builder
            ->add($domainsGroup)
            ->add('apply', SubmitType::class, [
                'label' => t('Apply'),
            ]);

        $builder->addModelTransformer(new CallbackTransformer(
            function ($data) {
                if ($data === null) {
                    $data = [];
                }

                return array_fill_keys($data, true);
            },
            function ($data) {
                return array_keys(array_filter($data));
            },
        ));
    }
}
