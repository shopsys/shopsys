<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Order;

use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionsType;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\OrderItemsType;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OrderAdmin extends AbstractAdmin
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
        parent::__construct();
    }

    /**
     * @param \Sonata\AdminBundle\Route\RouteCollectionInterface $collection
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form->tab('Basic Information')
                ->with('Order Information')
                    ->add('domainId', DisplayOnlyDomainIconType::class, [
                        'label' => t('Domain'),
                        'data' => $this->getSubject()->getDomainId(),
                    ])
                    ->add('number', DisplayOnlyType::class, [
                        'label' => t('Order number'),
                        'data' => $this->getSubject()->getNumber(),
                    ])
                    ->add('status', EntityType::class, [
                        'class' => 'Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus',
                        'choice_label' => 'name',
                    ])
                    ->add('note', TextareaType::class)
                ->end()
                ->with('Order Items')
                    ->add('orderItems', OrderItemsType::class, [
                        'order' => $this->getSubject(),
                    ])
                ->end()
            ->end()
            ->tab('Customer')
                ->with('Customer Information')
                    ->add('firstName')
                    ->add('lastName')
                    ->add('email')
                    ->add('telephone')
                ->end()
                ->with('Company information', ['class' => 'col-md-6'])
                    ->add('companyName')
                    ->add('companyNumber')
                    ->add('companyTaxNumber')
                ->end()
                ->with('Billing Address', ['class' => 'col-md-6'])
                    ->add('street')
                    ->add('city')
                    ->add('postcode')
                    ->add('country', EntityType::class, [
                        'class' => 'Shopsys\FrameworkBundle\Model\Country\Country',
                        'choice_label' => 'name',
                    ])
                ->end()
                ->with('Delivery Address', ['class' => 'col-md-6'])
                    ->add('deliveryAddressSameAsBillingAddress', ChoiceFieldMaskType::class, [
                        'choices' => [
                            'Yes' => true,
                            'No' => false,
                        ],
                        'required' => true,
                        'map' => [
                            false => [
                                'deliveryFirstName',
                                'deliveryLastName',
                                'deliveryCompanyName',
                                'deliveryStreet',
                                'deliveryCity',
                                'deliveryPostcode',
                                'deliveryCountry',
                            ],
                        ],
                    ])
                    ->add('deliveryFirstName', TextType::class)
                    ->add('deliveryLastName', TextType::class)
                    ->add('deliveryCompanyName', TextType::class)
                    ->add('deliveryStreet', TextType::class)
                    ->add('deliveryCity', TextType::class)
                    ->add('deliveryPostcode', TextType::class)
                    ->add('deliveryCountry', EntityType::class, [
                        'class' => 'Shopsys\FrameworkBundle\Model\Country\Country',
                        'choice_label' => 'name',
                    ])
                ->end()
            ->end()
            ->tab('Other')
                ->with('Payment transactions')
                    ->add('paymentTransactionRefunds', PaymentTransactionsType::class, [
                        'entry_type' => PaymentTransactionType::class,
                        'error_bubbling' => false,
                        'allow_add' => false,
                        'allow_delete' => false,
                        'required' => false,
                        'order' => $this->getSubject(),
                    ])
                ->end()
            ->end();
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('domainId', ChoiceFilter::class, [
            'label' => 'Domain',
            'show_filter' => true,
            'field_type' => ChoiceType::class,
            'field_options' => [
                'choices' => $this->domain->getAllDomainsAsChoices(),
            ],
        ]);
        $filter->add('number');
        $filter->add('email');
        $filter->add('createdAt', DateRangeFilter::class);
        $filter->add('totalPriceWithVat', NumberFilter::class);
        $filter->add('status', null, [
            'field_type' => EntityType::class,
            'field_options' => [
                'class' => 'Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus',
                'choice_label' => 'name',
            ],
        ]);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->remove('id');
        $list->add('number', null, [
            'label' => 'Order number',
        ]);
        $list->add('createdAt');
        $list->add('customerName');
        $list->add('domainId', 'domain', [
            'label' => 'Domain',
        ]);
        $list->add('status.name', 'status', [
            'label' => 'Status',
        ]);
        $list->add('totalPriceWithVat', null, [
            'label' => 'Total price',
            'template' => '@ShopsysAdministration/orderPrice.html.twig',
        ]);

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
                'delete' => [],
            ],
        ]);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $query */
        $query = parent::configureQuery($query);

        if ($this->isCurrentRoute('list')) {
            $query->addSelect('(CASE WHEN o.companyName IS NOT NULL
                    THEN o.companyName
                    ELSE CONCAT(o.lastName, \' \', o.firstName)
                END) AS customerName')
                ->andWhere('o.deleted = FALSE');
        }

        return $query;
    }

    /**
     * @param array $sortValues
     */
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
    }
}
