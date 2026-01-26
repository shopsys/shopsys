<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\CategorySeo\CategorySeoFilterFormType;
use Shopsys\FrameworkBundle\Form\Admin\CategorySeo\ReadyCategorySeoCombinationFormType;
use Shopsys\FrameworkBundle\Form\Admin\CategorySeo\ReadyCategorySeoCombinationSelectorFormType;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsContainBadDomainUrlException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixGridFactory;
use Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination;
use Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombinationFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[ForRole(AdminRoleConstant::ROLE_CATEGORY_SEO)]
class CategorySeoController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly CategorySeoFacade $categorySeoFacade,
        protected readonly ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly ReadyCategorySeoMixGridFactory $readyCategorySeoMixGridFactory,
        protected readonly Domain $domain,
        protected readonly SelectedCategorySeoMixCombinationFactory $selectedCategorySeoMixCombinationFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly ProductListOrderingModeForListFacade $productListOrderingModeForListFacade,
    ) {
    }

    #[Route(path: '/seo/category/')]
    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->readyCategorySeoMixGridFactory->create(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale(),
        );

        return $this->render('@ShopsysAdministration/content/categorySeo/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/seo/category/new/category')]
    #[CanCreate]
    public function newCategoryAction(): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $locale,
        );

        return $this->render('@ShopsysAdministration/content/categorySeo/newCategory.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'locale' => $locale,
        ]);
    }

    #[Route(path: '/seo/category/new/filters/category/{categoryId}', requirements: ['categoryId' => '\d+'])]
    #[CanCreate]
    public function newFiltersAction(Request $request, int $categoryId): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $category = $this->categoryFacade->getById($categoryId);
        $categorySeoFiltersData = new CategorySeoFiltersData();

        $form = $this->createCategorySeoFilterForm($category, $categorySeoFiltersData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->query->getBoolean('is_for_backlink')) {
            return $this->redirect(
                $this->getUrlWithCategoryIdAndAllQueryParameters(
                    'admin_categoryseo_newcombinations',
                    $categoryId,
                    $request->query->all(),
                    false,
                ),
            );
        }

        return $this->render('@ShopsysAdministration/content/categorySeo/newFilters.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'locale' => $locale,
        ]);
    }

    #[Route(path: '/seo/category/new/combinations/category/{categoryId}', requirements: ['categoryId' => '\d+'])]
    #[CanCreate]
    public function newCombinationsAction(Request $request, int $categoryId): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $category = $this->categoryFacade->getById($categoryId);

        $urlQuery = $request->query->all();

        $categorySeoFiltersData = new CategorySeoFiltersData();
        $filterForm = $this->createCategorySeoFilterForm($category, $categorySeoFiltersData);

        $filterForm->submit($urlQuery[$filterForm->getName()] ?? []);

        $combinationSelectorForm = $this->createReadyCategorySeoCombinationSelectorForm(
            $categorySeoFiltersData,
            $categoryId,
            $locale,
            $urlQuery,
        );

        $combinationSelectorForm->handleRequest($request);

        if ($combinationSelectorForm->isSubmitted() && $combinationSelectorForm->isValid()) {
            $formData = $combinationSelectorForm->getData();

            $parameterValueIdsByParameterIds = [];

            foreach ($categorySeoFiltersData->parameters as $parameter) {
                $parameterValueIdsByParameterIds[$parameter->getId()] = $formData['parameter_' . $parameter->getId()];
            }

            $selectedCategorySeoMixCombination = $this->selectedCategorySeoMixCombinationFactory->create(
                $this->adminDomainTabsFacade->getSelectedDomainId(),
                $categoryId,
                $formData['flagId'] ?? null,
                $formData['ordering'] ?? null,
                $parameterValueIdsByParameterIds,
            );

            return $this->redirectToRoute('admin_categoryseo_readycombination', [
                'categoryId' => $categoryId,
                'categorySeoFilterFormTypeAllQueries' => $urlQuery,
                'selectedCategorySeoMixCombinationJson' => $this->selectedCategorySeoMixCombinationFactory
                    ->createJsonFromSelectedCategorySeoMixCombination($selectedCategorySeoMixCombination),
            ]);
        }

        return $this->render('@ShopsysAdministration/content/categorySeo/newCombinations.html.twig', [
            'category' => $category,
            'locale' => $locale,
            'combinationSelectorForm' => $combinationSelectorForm->createView(),
        ]);
    }

    #[Route(path: '/seo/category/new/ready-combination/category/{categoryId}', requirements: ['categoryId' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function readyCombinationAction(Request $request, int $categoryId): Response
    {
        $categorySeoFilterFormTypeAllQueries = $request->query->all('categorySeoFilterFormTypeAllQueries') ?: null;
        $selectedCategorySeoMixCombinationJson = $request->query->get('selectedCategorySeoMixCombinationJson');

        if ($selectedCategorySeoMixCombinationJson === null) {
            // A little hack - when you need form sent data to create that same form - need for friendly URLs
            $sentReadyCategorySeoCombinationFormData = $request->request->all('ready_category_seo_combination_form');
            $selectedCategorySeoMixCombinationJson = $sentReadyCategorySeoCombinationFormData['selectedCategorySeoMixCombinationJson'] ?? null;

            $selectedCategorySeoMixCombination = $selectedCategorySeoMixCombinationJson === null ? null : $this->selectedCategorySeoMixCombinationFactory->createFromJson(
                $selectedCategorySeoMixCombinationJson,
            );
        } else {
            $selectedCategorySeoMixCombination = $this->selectedCategorySeoMixCombinationFactory->createFromJson(
                $selectedCategorySeoMixCombinationJson,
            );
        }

        $readyCategorySeoMixData = $this->readyCategorySeoMixDataFactory->createReadyCategorySeoMixData($selectedCategorySeoMixCombination);

        $this->storeJsonsToReadyCategorySeoMixData($readyCategorySeoMixData, $categorySeoFilterFormTypeAllQueries, $selectedCategorySeoMixCombination);

        if ($categorySeoFilterFormTypeAllQueries === null
            && $readyCategorySeoMixData->categorySeoFilterFormTypeAllQueriesJson !== null
        ) {
            $categorySeoFilterFormTypeAllQueries = json_decode($readyCategorySeoMixData->categorySeoFilterFormTypeAllQueriesJson, true, 512, JSON_THROW_ON_ERROR);
        }

        if ($categorySeoFilterFormTypeAllQueries !== null) {
            $newCombinationsUrl = $this->getUrlWithCategoryIdAndAllQueryParameters(
                'admin_categoryseo_newcombinations',
                $categoryId,
                $categorySeoFilterFormTypeAllQueries,
                false,
            );
        } else {
            $newCombinationsUrl = $this->generateUrl('admin_categoryseo_list');
        }

        $readyCategorySeoCombinationFormType = $this->createForm(ReadyCategorySeoCombinationFormType::class, $readyCategorySeoMixData, [
            'method' => 'POST',
            'new_combination_url' => $newCombinationsUrl,
            'readyCategorySeoMix' => $selectedCategorySeoMixCombination !== null ? $this->readyCategorySeoMixFacade->findBySelectedCategorySeoMixCombination($selectedCategorySeoMixCombination) : null,
        ]);

        $readyCategorySeoCombinationFormType->handleRequest($request);

        if ($readyCategorySeoCombinationFormType->isSubmitted() && $readyCategorySeoCombinationFormType->isValid()) {
            $this->readyCategorySeoMixDataFactory->fillValuesFromSelectedCategorySeoMixCombination(
                $readyCategorySeoMixData,
                $selectedCategorySeoMixCombination,
            );

            $selfUrl = $this->generateUrl(
                'admin_categoryseo_readycombination',
                [
                    'categoryId' => $categoryId,
                    'categorySeoFilterFormTypeAllQueries' => $categorySeoFilterFormTypeAllQueries,
                    'selectedCategorySeoMixCombinationJson' => $this->selectedCategorySeoMixCombinationFactory->createJsonFromSelectedCategorySeoMixCombination($selectedCategorySeoMixCombination),
                ],
            );

            try {
                $this->readyCategorySeoMixFacade->createOrEdit(
                    $selectedCategorySeoMixCombination,
                    $readyCategorySeoMixData,
                    $readyCategorySeoMixData->urls,
                );

                $this->addSuccessFlashTwig(
                    t('<strong><a href="{{ url }}">SEO category</a></strong> has been saved'),
                    ['url' => $selfUrl],
                );

                return $this->redirect($newCombinationsUrl);
            } catch (ReadyCategorySeoMixUrlsContainBadDomainUrlException) {
                $this->eventDispatcher->dispatch(new SilencedExceptionEvent());
                $this->addErrorFlash(t('Fill URL only for selected domain'));
            } catch (ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException) {
                $this->eventDispatcher->dispatch(new SilencedExceptionEvent());
                $this->addErrorFlash(t('Fill URL also for selected domain'));
            }
        }

        return $this->render('@ShopsysAdministration/content/categorySeo/readyCombination.html.twig', [
            'form' => $readyCategorySeoCombinationFormType->createView(),
            'categorySeoFilterFormTypeAllQueries' => $categorySeoFilterFormTypeAllQueries,
            'selectedCategorySeoMixCombination' => $selectedCategorySeoMixCombination,
            'flagName' => $selectedCategorySeoMixCombination->getFlagId() !== null ? $this->flagFacade->getById($selectedCategorySeoMixCombination->getFlagId())->getName() : '',
            'parameterValueNamesIndexedByParameterNames' => $this->parameterFacade->getParameterValueNamesIndexedByParameterNames(
                $selectedCategorySeoMixCombination->getParameterValueIdsByParameterIds(),
            ),
            'selectedCategorySeoMixCombinationDomainConfig' => $this->domain->getDomainConfigById($selectedCategorySeoMixCombination->getDomainId()),
        ]);
    }

    #[Route(path: '/seo/category/ready-combination/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($id);
            $this->readyCategorySeoMixFacade->delete($readyCategorySeoMix);
            $this->addSuccessFlashTwig(
                t('SEO combination of category with ID {{ ReadyCategorySeoMixId }} has been removed', [
                    '{{ ReadyCategorySeoMixId }}' => $id,
                ]),
            );
        } catch (ReadyCategorySeoMixNotFoundException) {
            $this->addSuccessFlashTwig(
                t('SEO combination of category with ID {{ ReadyCategorySeoMixId }} has not been removed, because it was not found', [
                    '{{ ReadyCategorySeoMixId }}' => $id,
                ]),
            );
        }

        return $this->redirectToRoute('admin_categoryseo_list');
    }

    protected function createCategorySeoFilterForm(
        Category $category,
        CategorySeoFiltersData $categorySeoFiltersData,
    ): FormInterface {
        return $this->createForm(CategorySeoFilterFormType::class, $categorySeoFiltersData, [
            'category' => $category,
            'domainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
            'method' => 'GET',
        ]);
    }

    protected function createReadyCategorySeoCombinationSelectorForm(
        CategorySeoFiltersData $categorySeoFiltersData,
        int $categoryId,
        string $locale,
        array $urlQuery,
    ): FormInterface {
        $parameterValueChoices = [];

        foreach ($categorySeoFiltersData->parameters as $parameter) {
            $parameterValues = $this->parameterFacade->getParameterValuesByParameterAndLocale($parameter, $locale);

            $choices = [];

            foreach ($parameterValues as $parameterValue) {
                $choices[$parameterValue->getText()] = $parameterValue->getId();
            }

            $parameterValueChoices[$parameter->getId()] = $choices;
        }

        $flagChoices = [];

        if ($categorySeoFiltersData->useFlags) {
            $flags = $this->flagFacade->getAllVisibleFlags($locale);

            foreach ($flags as $flag) {
                $flagChoices[$flag->getName($locale)] = $flag->getId();
            }
        }

        $orderingChoices = [];

        if ($categorySeoFiltersData->useOrdering) {
            $orderingChoices = array_flip(
                $this->productListOrderingModeForListFacade->getProductListOrderingConfig()
                    ->getSupportedOrderingModesNamesIndexedById(),
            );
        }

        $backLinkUrl = $this->getUrlWithCategoryIdAndAllQueryParameters(
            'admin_categoryseo_newfilters',
            $categoryId,
            $urlQuery,
            true,
        );

        return $this->createForm(ReadyCategorySeoCombinationSelectorFormType::class, null, [
            'categorySeoFiltersData' => $categorySeoFiltersData,
            'parameterValueChoices' => $parameterValueChoices,
            'flagChoices' => $flagChoices,
            'orderingChoices' => $orderingChoices,
            'backLink' => $backLinkUrl,
        ]);
    }

    protected function getUrlWithCategoryIdAndAllQueryParameters(
        string $routeName,
        int $categoryId,
        array $categorySeoFilterFormTypeAllQueries,
        bool $isForBackLink,
    ): string {
        return $this->generateUrl(
            $routeName,
            array_merge(
                ['categoryId' => $categoryId],
                $categorySeoFilterFormTypeAllQueries,
                ['is_for_backlink' => $isForBackLink],
            ),
        );
    }

    protected function storeJsonsToReadyCategorySeoMixData(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        ?array $categorySeoFilterFormTypeAllQueries,
        ?SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination,
    ): void {
        if (isset($categorySeoFilterFormTypeAllQueries)) {
            $readyCategorySeoMixData->categorySeoFilterFormTypeAllQueriesJson = json_encode($categorySeoFilterFormTypeAllQueries, JSON_THROW_ON_ERROR);
        }

        if (isset($selectedCategorySeoMixCombination)) {
            $readyCategorySeoMixData->selectedCategorySeoMixCombinationJson = $this->selectedCategorySeoMixCombinationFactory->createJsonFromSelectedCategorySeoMixCombination($selectedCategorySeoMixCombination);
        }
    }
}
