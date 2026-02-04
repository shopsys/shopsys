<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\CategorySeo\CategorySeoFilterFormType;
use Shopsys\FrameworkBundle\Form\Admin\CategorySeo\ReadyCategorySeoCombinationFormType;
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
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_CATEGORY_SEO)]
class CategorySeoController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFacade $categorySeoFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixGridFactory $readyCategorySeoMixGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombinationFactory $selectedCategorySeoMixCombinationFactory
     */
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
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/seo/category/new/combinations/category/{categoryId}', requirements: ['categoryId' => '\d+'])]
    #[CanCreate]
    public function newCombinationsAction(Request $request, int $categoryId): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $category = $this->categoryFacade->getById($categoryId);
        $categorySeoFiltersData = new CategorySeoFiltersData();

        $form = $this->createCategorySeoFilterForm($category, $categorySeoFiltersData);
        $form->handleRequest($request);

        $categorySeoMixes = $this->categorySeoFacade->getCategorySeoMixes(
            $category,
            $categorySeoFiltersData,
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale(),
        );

        $selectedCategorySeoMixCombinations = [];

        foreach ($categorySeoMixes as $key => $categorySeoMix) {
            $parameterValueIdsByParameterIds = [];

            foreach ($categorySeoMix->getParameterValues() as $index => $parameterValue) {
                $parameterValueIdsByParameterIds[$categorySeoFiltersData->parameters[$index]->getId()] = $parameterValue->getId();
            }

            $selectedCategorySeoMixCombinationArray = $this->selectedCategorySeoMixCombinationFactory->createArray(
                $categorySeoMix->getDomainId(),
                $categorySeoMix->getCategory()->getId(),
                $categorySeoMix->getFlag()?->getId(),
                $categorySeoMix->getOrdering(),
                $parameterValueIdsByParameterIds,
            );

            $selectedCategorySeoMixCombinations[$key] = $this->selectedCategorySeoMixCombinationFactory->createFromArray($selectedCategorySeoMixCombinationArray);
        }

        return $this->render('@ShopsysAdministration/content/categorySeo/newCombinations.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'categorySeoMixes' => $categorySeoMixes,
            'categorySeoFiltersData' => $categorySeoFiltersData,
            'locale' => $locale,
            'backLink' => $this->getUrlWithCategoryIdAndAllQueryParameters(
                'admin_categoryseo_newfilters',
                $categoryId,
                $request->query->all(),
                true,
            ),
            'categorySeoFilterFormTypeAllQueries' => $request->query->all(),
            'categoryId' => $categoryId,
            'selectedCategorySeoMixCombinations' => $selectedCategorySeoMixCombinations,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
                $this->addErrorFlash(t('Fill URL only for selected domain'));
            } catch (ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException) {
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

    /**
     * @param int $categoryId
     * @param array $categorySeoFilterFormTypeAllQueries
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function readyCombinationButtonAction(
        int $categoryId,
        array $categorySeoFilterFormTypeAllQueries,
        SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination,
    ): Response {
        return $this->render('@ShopsysAdministration/content/categorySeo/readyCombinationEditButton.html.twig', [
            'existsReadyCategorySeoMix' => $this->readyCategorySeoMixFacade->findBySelectedCategorySeoMixCombination($selectedCategorySeoMixCombination) !== null,
            'categoryId' => $categoryId,
            'categorySeoFilterFormTypeAllQueries' => $categorySeoFilterFormTypeAllQueries,
            'selectedCategorySeoMixCombination' => $selectedCategorySeoMixCombination,
            'selectedCategorySeoMixCombinationJson' => $this->selectedCategorySeoMixCombinationFactory->createJsonFromSelectedCategorySeoMixCombination($selectedCategorySeoMixCombination),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \Symfony\Component\Form\FormInterface
     */
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

    /**
     * @param string $routeName
     * @param int $categoryId
     * @param array $categorySeoFilterFormTypeAllQueries
     * @param bool $isForBackLink
     * @return string
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param array|null $categorySeoFilterFormTypeAllQueries
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination|null $selectedCategorySeoMixCombination
     */
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
