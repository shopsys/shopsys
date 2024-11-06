<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PriceList;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class PriceListFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $priceList = $options['priceList'];

        if ($priceList instanceof PriceList) {
            $builder->add('id', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $priceList->getId(),
            ]);
        }

        $this->addDomainIconField($builder, $priceList);

        $builder
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter product list name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Product list name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('priceList')
            ->setAllowedTypes('priceList', [PriceList::class, 'null'])
            ->setDefaults([
                'data_class' => PriceListData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList|null $priceList
     */
    private function addDomainIconField(FormBuilderInterface $builder, ?PriceList $priceList): void
    {
        if (!$this->domain->isMultidomain()) {
            return;
        }

        if ($priceList instanceof PriceList) {
            $builder->add('domainIcon', DisplayOnlyDomainIconType::class, [
                'label' => t('Domain'),
                'data' => $priceList->getDomainId(),
            ]);
        } else {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ]);
        }
    }
}
