<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\NotInArray;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Seo\Organization;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationData;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SeoSettingFormType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
        private readonly SeoSettingFacade $seoSettingFacade,
        private readonly OrganizationFacade $organizationFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $titleAddOnsOnOtherDomains = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId !== $options['domain_id']) {
                $titleAddOnsOnOtherDomains[] = $this->seoSettingFacade->getTitleAddOn($domainId);
            }
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add('titleAddOn', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotInArray(
                        array: array_diff($titleAddOnsOnOtherDomains, [null]),
                        message: 'Same title complement is used on another domain',
                    ),
                ],
                'label' => 'Complement to title',
                'help' => t(
                    'Complement to title will be set as suffix to all titles e.g. if complement is set “ | My shop” and product name is “iPhone 7” the result title for this products page will be “iPhone 7 | My shop”.',
                ),
            ]);

        $builder->add($builderSettingsGroup);
        $this->addOrganizationFields($builder, $options['domain_id']);

        $builder
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    private function addOrganizationFields(FormBuilderInterface $builder, int $domainId): void
    {
        $organization = $builder->create('organization', GroupType::class, [
            'inherit_data' => false,
            'data_class' => OrganizationData::class,
            'label' => 'Organization',
            'required' => false,
        ]);

        $organization->add('name', TextType::class, [
            'label' => 'Company name',
            'required' => false,
        ])
            ->add('companyTaxNumber', TextType::class, [
                'label' => 'Tax identification number',
                'required' => false,
                'help' => t('General tax identification number (DIČ in Slovakia, e.g. 2120123456). Enter the VAT identification number separately.'),
            ])
            ->add('companyVatNumber', TextType::class, [
                'label' => 'VAT identification number',
                'required' => false,
                'help' => t('VAT identification number including the country prefix (IČ DPH in Slovakia, e.g. SK2120123456). Leave empty if the organization has no VAT identification number.'),
            ])
            ->add('companyNumber', TextType::class, [
                'label' => 'Company number',
                'required' => false,
                'help' => t('Company registration number, e.g. 12345678. Do not confuse it with the tax identification number or VAT identification number.'),
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Company description',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'Street and house number',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'required' => false,
            ])
            ->add('postcode', TextType::class, [
                'label' => 'Postcode',
                'required' => false,
            ])
            ->add('country', TextType::class, [
                'label' => 'Country',
                'required' => false,
            ])
            ->add('image', ImageUploadType::class, [
                'entity' => $this->organizationFacade->findByDomainId($domainId),
                'image_entity_class' => Organization::class,
                'label' => 'Organization logo',
                'required' => false,
                'file_constraints' => [
                    new Constraints\Image(maxSize: '8M', extensions: ['jpg', 'jpeg', 'png'], minWidth: 200, minHeight: 200),
                ],
                'info_text' => t('JPG or PNG, up to 8 MB. Recommended size: 1200×630 px (1.91:1), acceptable minimum: 600×315 px, required minimum: 200×200 px.'),
            ]);

        $builder->add($organization);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->addAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
