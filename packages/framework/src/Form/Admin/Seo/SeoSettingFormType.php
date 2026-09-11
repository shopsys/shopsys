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
use Shopsys\FrameworkBundle\Model\Seo\OrganizationSettingFacade;
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
        private readonly OrganizationSettingFacade $organizationSettingFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $titlesOnOtherDomains = [];
        $titleAddOnsOnOtherDomains = [];
        $descriptionsOnOtherDomains = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId !== $options['domain_id']) {
                $titlesOnOtherDomains[] = $this->seoSettingFacade->getTitleMainPage($domainId);
                $titleAddOnsOnOtherDomains[] = $this->seoSettingFacade->getTitleAddOn($domainId);
                $descriptionsOnOtherDomains[] = $this->seoSettingFacade->getDescriptionMainPage($domainId);
            }
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add('title', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotInArray(
                        array: array_diff($titlesOnOtherDomains, [null]),
                        message: 'Same title is used on another domain',
                    ),
                ],
                'label' => 'Headline',
            ])
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
            ])
            ->add('metaDescription', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new NotInArray(
                        array: array_diff($descriptionsOnOtherDomains, [null]),
                        message: 'Same description is used on another domain',
                    ),
                ],
                'label' => 'Meta description',
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
        ]);
        $organization->add('companyTaxNumber', TextType::class, [
            'label' => 'Tax number',
            'required' => false,
        ]);
        $organization->add('companyNumber', TextType::class, [
            'label' => 'Company number',
            'required' => false,
        ]);
        $organization->add('description', TextareaType::class, [
            'label' => 'Company description',
            'required' => false,
        ]);
        $organization->add('street', TextType::class, [
            'label' => 'Street and house number',
            'required' => false,
        ]);
        $organization->add('city', TextType::class, [
            'label' => 'City',
            'required' => false,
        ]);
        $organization->add('postcode', TextType::class, [
            'label' => 'Postcode',
            'required' => false,
        ]);
        $organization->add('country', TextType::class, [
            'label' => 'Country',
            'required' => false,
        ]);

        $this->addOrganizationLogoFields($organization, $domainId);
        $builder->add($organization);
    }

    private function addOrganizationLogoFields(FormBuilderInterface $builder, int $domainId): void
    {
        $builder
            ->add('image', ImageUploadType::class, [
                'entity' => $this->organizationSettingFacade->findByDomainId($domainId),
                'image_entity_class' => Organization::class,
                'label' => 'Organization logo',
                'required' => false,
                'file_constraints' => [
                    new Constraints\Image(maxSize: '8M', extensions: ['jpg', 'jpeg', 'png'], minWidth: 200, minHeight: 200),
                ],
                'info_text' => t('JPG or PNG, up to 8 MB. Recommended size: 1200×630 px (1.91:1), acceptable minimum: 600×315 px, required minimum: 200×200 px.'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
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
