<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use App\Form\Admin\CategorySeoFilterFormType;
use App\Form\Admin\ReadyCategorySeoCombinationFormType;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\CategorySeo\CategorySeoFacade;
use App\Model\CategorySeo\CategorySeoFiltersData;
use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsContainBadDomainUrlException;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException;
use App\Model\CategorySeo\ReadyCategorySeoMixData;
use App\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\CategorySeo\ReadyCategorySeoMixGridFactory;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoCategoryController extends CRUDController
{
    use FlashMessageTrait;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\CategorySeo\CategorySeoFacade $categorySeoFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixGridFactory $readyCategorySeoMixGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        private readonly CategoryFacade $categoryFacade,
        private readonly CategorySeoFacade $categorySeoFacade,
        private readonly ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
        private readonly ParameterFacade $parameterFacade,
        private readonly FlagFacade $flagFacade,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        private readonly ReadyCategorySeoMixGridFactory $readyCategorySeoMixGridFactory,
        private readonly Domain $domain, private readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

//    /**
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     * @return \Symfony\Component\HttpFoundation\Response
//     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
//     */
//    public function listAction(Request $request): Response
//    {
//        $grid = $this->readyCategorySeoMixGridFactory->create(
//            $this->adminDomainTabsFacade->getSelectedDomainId(),
//            $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale(),
//        );
//
//        return $this->render('@ShopsysAdministration/categorySeo/list.html.twig', [
//            'gridView' => $grid->createView(),
//        ]);
//    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $locale,
        );

        return $this->render('@ShopsysAdministration/categorySeo/newCategory.html.twig', [
            'action' => 'create',
            'objectId' => null,
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'locale' => $locale,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/%admin_url%-new/seo/category/new/filters/category/{categoryId}', name: 'admin_new_categoryseo_newfilters', requirements: ['categoryId' => '\d+'])]
    public function newFiltersAction(Request $request, int $categoryId): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $category = $this->categoryFacade->getById($categoryId);
        $categorySeoFiltersData = new CategorySeoFiltersData();

        $form = $this->createCategorySeoFilterForm($category, $categorySeoFiltersData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->get('is_for_backlink', false) === false) {
            return $this->redirect(
                $this->getUrlWithCategoryIdAndAllQueryParameters(
                    'admin_new_categoryseo_newcombinations',
                    $categoryId,
                    $request->query->all(),
                    false,
                )
            );
        }

        return $this->render('@ShopsysAdministration/categorySeo/newFilters.html.twig', [
            'action' => 'newFilters',
            'objectId' => null,
            'object' => null,
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
    #[Route('/%admin_url%-new/seo/category/new/combinations/category/{categoryId}', name: 'admin_new_categoryseo_newcombinations', requirements: ['categoryId' => '\d+'])]
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

        return $this->render('@ShopsysAdministration/categorySeo/newCombinations.html.twig', [
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
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/%admin_url%-new/seo/category/new/ready-combination/category/{categoryId}', requirements: ['categoryId' => '\d+'])]
    public function readyCombinationAction(Request $request, int $categoryId): Response
    {
//        $this->readyCategorySeoMixFacade->getById($categoryId);


        $categorySeoFilterFormTypeAllQueries = $request->get('categorySeoFilterFormTypeAllQueries');

        $choseCategorySeoMixCombination = ChoseCategorySeoMixCombination::createFromJson(
            $request->get('choseCategorySeoMixCombinationJson'),
        );
        // A little hack - when you need form sent data to create that same form - need for friendly URLs
        if ($choseCategorySeoMixCombination === null) {
            $sentReadyCategorySeoCombinationFormData = $request->get('ready_category_seo_combination_form');
            $choseCategorySeoMixCombination = ChoseCategorySeoMixCombination::createFromJson(
                $sentReadyCategorySeoCombinationFormData['choseCategorySeoMixCombinationJson'],
            );
        }

        $readyCategorySeoMixData = $this->readyCategorySeoMixDataFactory->createReadyCategorySeoMixData($categoryId, $choseCategorySeoMixCombination);

        $this->storeJsonsToReadyCategorySeoMixData($readyCategorySeoMixData, $categorySeoFilterFormTypeAllQueries, $choseCategorySeoMixCombination);

        $readyCategorySeoCombinationFormType = $this->createForm(ReadyCategorySeoCombinationFormType::class, $readyCategorySeoMixData, [
            'method' => 'POST',
            'readyCategorySeoMix' => $choseCategorySeoMixCombination !== null ? $this->readyCategorySeoMixFacade->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination) : null,
        ]);

        $readyCategorySeoCombinationFormType->handleRequest($request);

        if ($categorySeoFilterFormTypeAllQueries === null
            && $readyCategorySeoMixData->categorySeoFilterFormTypeAllQueriesJson !== null
        ) {
            $categorySeoFilterFormTypeAllQueries = json_decode($readyCategorySeoMixData->categorySeoFilterFormTypeAllQueriesJson, true);
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

        if ($readyCategorySeoCombinationFormType->isSubmitted() && $readyCategorySeoCombinationFormType->isValid()) {
            $this->readyCategorySeoMixDataFactory->fillValuesFromChoseCategorySeoMixCombination(
                $readyCategorySeoMixData,
                $choseCategorySeoMixCombination,
            );

            $selfUrl = $this->generateUrl(
                'admin_categoryseo_readycombination',
                [
                    'categoryId' => $categoryId,
                    'categorySeoFilterFormTypeAllQueries' => $categorySeoFilterFormTypeAllQueries,
                    'choseCategorySeoMixCombinationJson' => $choseCategorySeoMixCombination->getInJson(),
                ],
            );

            try {
                $this->readyCategorySeoMixFacade->createOrEdit(
                    $choseCategorySeoMixCombination,
                    $readyCategorySeoMixData,
                    $readyCategorySeoMixData->urls,
                );

                $this->addSuccessFlashTwig(
                    t('<strong><a href="{{ url }}">SEO category</a></strong> has been saved'),
                    ['url' => $selfUrl],
                );

                return $this->redirect($newCombinationsUrl);
            } catch (ReadyCategorySeoMixUrlsContainBadDomainUrlException $exception) {
                $this->addErrorFlash(t('Fill URL only for selected domain'));
            } catch (ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException $exception) {
                $this->addErrorFlash(t('Fill URL also for selected domain'));
            }
        }

        return $this->render('@ShopsysAdministration/categorySeo/readyCombination.html.twig', [
            'form' => $readyCategorySeoCombinationFormType->createView(),
            'categorySeoFilterFormTypeAllQueries' => $categorySeoFilterFormTypeAllQueries,
            'newCombinationsUrl' => $newCombinationsUrl,
            'choseCategorySeoMixCombination' => $choseCategorySeoMixCombination,
            'flagName' => $choseCategorySeoMixCombination->getFlagId() !== null ? $this->flagFacade->getById($choseCategorySeoMixCombination->getFlagId())->getName() : '',
            'parameterValueNamesIndexedByParameterNames' => $this->parameterFacade->getParameterValueNamesIndexedByParameterNames(
                $choseCategorySeoMixCombination->getParameterValueIdsByParameterIds(),
            ),
            'choseCategorySeoMixCombinationDomainConfig' => $this->domain->getDomainConfigById($choseCategorySeoMixCombination->getDomainId()),
        ]);
    }

    /**
     * @param int $categoryId
     * @param array $categorySeoFilterFormTypeAllQueries
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function readyCombinationButtonAction(
        int $categoryId,
        array $categorySeoFilterFormTypeAllQueries,
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): Response {
        return $this->render('@ShopsysAdministration/categorySeo/readyCombinationEditButton.html.twig', [
            'existsReadyCategorySeoMix' => $this->readyCategorySeoMixFacade->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination) !== null,
            'categoryId' => $categoryId,
            'categorySeoFilterFormTypeAllQueries' => $categorySeoFilterFormTypeAllQueries,
            'choseCategorySeoMixCombination' => $choseCategorySeoMixCombination,
            'choseCategorySeoMixCombinationJson' => $choseCategorySeoMixCombination->getInJson(),
        ]);
    }

//    /**
//     * @CsrfProtection
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     * @param int $id
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    #[Route('/%admin_url%-new/seo/category/ready-combination/delete/{id}', requirements: ['id' => '\d+'])]
//    public function deleteAction(Request $request): Response
//    {
//        $existingObject = $this->assertObjectExists($request, true);
//        $id = (int)$this->admin->getNormalizedIdentifier($existingObject);
//
//        try {
//            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($id);
//            $this->readyCategorySeoMixFacade->delete($readyCategorySeoMix);
//            $this->addSuccessFlashTwig(
//                t('SEO combination of category with ID {{ ReadyCategorySeoMixId }} has been removed', [
//                    '{{ ReadyCategorySeoMixId }}' => $id,
//                ]),
//            );
//        } catch (ReadyCategorySeoMixNotFoundException $readyCategorySeoMixNotFoundException) {
//            $this->addSuccessFlashTwig(
//                t('SEO combination of category with ID {{ ReadyCategorySeoMixId }} has not been removed, because it was not found', [
//                    '{{ ReadyCategorySeoMixId }}' => $id,
//                ]),
//            );
//        }
//
//        return $this->redirectToRoute('admin_categoryseo_list');
//    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createCategorySeoFilterForm(
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
    private function getUrlWithCategoryIdAndAllQueryParameters(
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
                ['_sonata_admin' => 'admin.seo_category'],
            ),
        );
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param array|null $categorySeoFilterFormTypeAllQueries
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination|null $choseCategorySeoMixCombination
     */
    private function storeJsonsToReadyCategorySeoMixData(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        ?array $categorySeoFilterFormTypeAllQueries,
        ?ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): void {
        if (isset($categorySeoFilterFormTypeAllQueries)) {
            $readyCategorySeoMixData->categorySeoFilterFormTypeAllQueriesJson = json_encode($categorySeoFilterFormTypeAllQueries);
        }

        if (isset($choseCategorySeoMixCombination)) {
            $readyCategorySeoMixData->choseCategorySeoMixCombinationJson = $choseCategorySeoMixCombination->getInJson();
        }
    }
}
