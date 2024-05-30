<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\SeoCategory;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Category\CategoryTranslation;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class SeoCategoryAdmin extends AbstractAdmin
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct();
    }

    /**
     * @param array $buttonList
     * @param string $action
     * @param object|null $object
     * @return array
     */
    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $buttonList['new'] = ['template' => '@ShopsysAdministration/newButton.html.twig'];
        unset($buttonList['create']);

        return $buttonList;
    }

    /**
     * @param \Sonata\AdminBundle\Route\RouteCollectionInterface $collection
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('new');
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('category.name', null, ['label' => t('Category name')]);
        $list->add('friendlyUrlSlug', null, ['label' => t('Main URL')]);
        $list->add('choseCategorySeoMixCombinationJson', null, ['label' => t('Combination of parameters and their values')]);
        $list->add('flag.name', null, ['label' => t('Flag')]);
        $list->add('ordering', null, ['label' => t('Ordering')]);

//        $grid->addEditActionColumn('admin_categoryseo_readycombination', [
//            'categoryId' => 'categoryId',
//            'choseCategorySeoMixCombinationJson' => 'rcsm.choseCategorySeoMixCombinationJson',
//        ]);
//        $grid->addDeleteActionColumn('admin_categoryseo_delete', ['id' => 'rcsmId']);

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'new' => [],
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
        $domainConfig = $this->adminDomainTabsFacade->getSelectedDomainConfig();
        $domainId = $domainConfig->getId();

        $query
            ->addSelect('fu.slug as friendlyUrlSlug')
            ->leftJoin(FriendlyUrl::class, 'fu', Join::WITH, 'fu.routeName = :routeName and fu.entityId = o.id and fu.domainId = :domainId and fu.main = true')
            ->setParameter('routeName', 'front_category_seo')
            ->setParameter('domainId', $domainId)
            ->orderBy('o.id', 'DESC');

        return $query;

//
//        $queryBuilder = $this->em->createQueryBuilder()
//            ->select('rcsm, rcsm.id as rcsmId, c.id as categoryId, ct.name as categoryName, fu.slug as friendlyUrlSlug, rcsm.choseCategorySeoMixCombinationJson, ft.name as flagName, rcsm.ordering')
//            ->from(ReadyCategorySeoMix::class, 'rcsm')
//            ->andWhere('rcsm.domainId = :domainId')
//            ->join('rcsm.category', 'c')
//            ->leftJoin(CategoryTranslation::class, 'ct', Join::WITH, 'ct.translatable = c and ct.locale = :locale ')
//            ->leftJoin(FriendlyUrl::class, 'fu', Join::WITH, 'fu.routeName = :routeName and fu.entityId = rcsm.id and fu.domainId = :domainId and fu.main = true')
//            ->leftJoin('rcsm.flag', 'f')
//            ->leftJoin(FlagTranslation::class, 'ft', Join::WITH, 'ft.translatable = f and ft.locale = :locale')
//            ->setParameter('locale', $locale)
//            ->setParameter('domainId', $domainId)
//            ->setParameter('routeName', 'front_category_seo')
//            ->orderBy('rcsm.id', 'DESC');

//        return new ProxyQuery($queryBuilder);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData */
        $readyCategorySeoMixData = $this->getSubject();

        if ($readyCategorySeoMixData instanceof ReadyCategorySeoMixData) {
            $id = $readyCategorySeoMixData->id;
        } else {
            $id = $readyCategorySeoMixData->getId();
        }

        $form
            ->add('urls', UrlListType::class, [
                'required' => true,
                'route_name' => 'front_category_seo',
                'entity_id' => $id,
                'label' => t('URL Settings'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('h1', TextType::class, [
                'label' => t('Heading (H1)'),
                'required' => true,
            ])
            ->add('showInCategory', YesNoType::class, [
                'label' => t('Show in the category'),
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => t('Short description of category'),
                'required' => false,
            ])
            ->add('description', CKEditorType::class, [
                'label' => t('Category description'),
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => t('Page title'),
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros',
                    'recommended_length' => 60,
                ],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => t('Meta description'),
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros',
                    'recommended_length' => 155,
                ],
            ])
            ->add('categorySeoFilterFormTypeAllQueriesJson', HiddenType::class)
            ->add('choseCategorySeoMixCombinationJson', HiddenType::class);
    }
}
