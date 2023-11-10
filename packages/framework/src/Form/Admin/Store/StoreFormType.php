<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\Admin\Store\OpeningHours\OpeningHoursFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreData;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFriendlyUrlProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StoreFormType extends AbstractType
{
    private ?Store $store = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        private readonly StockFacade $stockFacade,
        private readonly StoreFacade $storeFacade,
        private readonly CountryFacade $countryFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['store'] instanceof Store) {
            $this->store = $options['store'];

            $builder
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['store']->getId(),
                    'label' => t('ID'),
                ])
                ->add('isDefault', DisplayOnlyType::class, [
                    'required' => false,
                    'data' => $options['store']->isDefault() ? t('Yes') : t('No'),
                    'label' => t('Default store'),
                ])
                ->add('urls', UrlListType::class, [
                    'route_name' => StoreFriendlyUrlProvider::ROUTE_NAME,
                    'entity_id' => $options['store']->getId(),
                    'label' => t('URL settings'),
                ]);
        }

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                    ),
                ],
                'label' => t('Name'),
            ])
            ->add('isEnabledOnDomains', DomainsType::class, [
                'required' => false,
                'label' => t('Display on'),
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'External ID cannot be longer than {{ limit }} characters'],
                    ),
                    new Constraints\Callback([$this, 'sameStoreExternalIdValidation']),
                ],
                'label' => t('External ID'),
            ])
            ->add('stock', ChoiceType::class, [
                'required' => false,
                'label' => t('Warehouse'),
                'placeholder' => t('No warehouse associated'),
                'choices' => $this->stockFacade->getAllStocks(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('description', CKEditorType::class, [
                'required' => false,
            ])
            ->add('locationLatitude', TextType::class, [
                'required' => false,
            ])
            ->add('locationLongitude', TextType::class, [
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => t('Street'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter street']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Street name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => t('City'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter city']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'City name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('postcode', TextType::class, [
                'label' => t('Postcode'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter zip code']),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'label' => t('Country'),
                'required' => true,
                'choices' => $this->countryFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose country']),
                ],
            ])
            ->add('openingHours', CollectionType::class, [
                'label' => t('Opening hours'),
                'entry_type' => OpeningHoursFormType::class,
                'required' => false,
            ])
            ->add('contactInfo', TextareaType::class, [
                'required' => false,
            ])
            ->add('specialMessage', TextareaType::class, [
                'required' => false,
            ])
            ->add($this->createImagesGroup($builder, $options))
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['store'])
            ->setAllowedTypes('store', [Store::class, 'null'])
            ->setDefaults([
                'data_class' => StoreData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param string|null $externalId
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function sameStoreExternalIdValidation(?string $externalId, ExecutionContextInterface $context): void
    {
        if ($externalId === null) {
            return;
        }

        if ($this->store !== null && $externalId === $this->store->getExternalId()) {
            return;
        }

        $store = $this->storeFacade->findStoreByExternalId($externalId);

        if ($store !== null) {
            $context->addViolation('Store with this external ID already exists');
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createImagesGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder->create('imageGroup', GroupType::class, [
            'label' => t('Images'),
        ])->add('image', ImageUploadType::class, [
            'required' => false,
            'image_entity_class' => Store::class,
            'file_constraints' => [
                new Constraints\Image([
                    'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                    'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                        . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                ]),
            ],
            'label' => t('Upload image'),
            'entity' => $options['store'],
            'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
        ]);
    }
}
