<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Form\Admin\SaleCategoryFormType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Controller\Admin\CategoryController as BaseCategoryController;
use Shopsys\FrameworkBundle\Form\Admin\Category\TopCategory\TopCategoriesFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Category\CategoryDataFactory $categoryDataFactory
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \App\Component\Domain\Domain $domain
 */
class CategoryController extends BaseCategoryController
{
    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryDataFactoryInterface $categoryDataFactory,
        SessionInterface $session,
        Domain $domain,
        BreadcrumbOverrider $breadcrumbOverrider,
        FormBuilderHelper $formBuilderHelper,
        AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
        parent::__construct(
            $categoryFacade,
            $categoryDataFactory,
            $session,
            $domain,
            $breadcrumbOverrider
        );
        $this->formBuilderHelper = $formBuilderHelper;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @Route("/category/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function listAction(Request $request)
    {
        if (count($this->domain->getAll()) > 1) {
            if ($request->query->has('domain')) {
                $domainId = (int)$request->query->get('domain');
            } else {
                $domainId = (int)$this->session->get('categories_selected_domain_id', static::ALL_DOMAINS);
            }
        } else {
            $domainId = static::ALL_DOMAINS;
        }

        if ($domainId !== static::ALL_DOMAINS) {
            try {
                $this->domain->getDomainConfigById($domainId);
            } catch (\Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException $ex) {
                $domainId = static::ALL_DOMAINS;
            }
        }

        $this->session->set('categories_selected_domain_id', $domainId);

        if ($domainId === static::ALL_DOMAINS) {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getAllCategoriesWithPreloadedChildren($request->getLocale());
        } else {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain($domainId, $request->getLocale());
        }

        return $this->render('/Admin/Content/Category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'isForAllDomains' => ($domainId === static::ALL_DOMAINS),
            'disabledFormFields' => $this->formBuilderHelper->hasFormDisabledFields(),
        ]);
    }

    /**
     * @Route("/category/sale-category/setting/", name="admin_set_sale_category")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setSaleCategoryAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $formData = [
            'category' => $this->categoryFacade->findSaleCategory(),
        ];

        $form = $this->createForm(SaleCategoryFormType::class, $formData, [
            'domain_id' => $domainId
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saleCategory = $form->getData()['category'];

            $this->categoryFacade->saveSaleCategory($saleCategory);

            $this->addSuccessFlash(t('Nastavení výprodejové kategorie bylo úspěšné'));
        }

        return $this->render('Admin/Content/Blog/Category/saleCategoryList.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
