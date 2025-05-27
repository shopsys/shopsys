import { CollapsibleDescriptionWithImage } from 'app/_components/Blocks/CollapsibleDescriptionWithImage/CollapsibleDescriptionWithImage';
import { FilteredProductsWrapper } from 'app/_components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { FilterPanelSection } from 'app/_components/Blocks/Product/Filter/FilterPanelSection';
import { FilterSelectedParameters } from 'app/_components/Blocks/Product/Filter/FilterSelectedParameters';
import { getEndCursor } from 'app/_components/Blocks/Product/Filter/utils/getEndCursor';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { productListTwClass } from 'app/_components/Blocks/Product/ProductsList/ProductsList';
import { SimpleNavigation } from 'app/_components/Blocks/SimpleNavigation/SimpleNavigation';
import { FilterAndSortingBarWrapper } from 'app/_components/Blocks/SortingBar/FilterAndSortingBarWrapper';
import { AdvancedSeoCategories } from 'app/_components/Page/CategoryDetail/AdvancedSeoCategories';
import { CategoryBestsellers } from 'app/_components/Page/CategoryDetail/CategoryBestsellers/CategoryBestsellers';
import { CategoryDetailContent } from 'app/_components/Page/CategoryDetail/CategoryDetailContent';
import { CategoryDetailPagination } from 'app/_components/Page/CategoryDetail/CategoryDetailPagination';
import { CategoryDetailProductsServer } from 'app/_components/Page/CategoryDetail/CategoryDetailProductsServer';
import { getCategoryDetailQuery } from 'app/_queries/getCategoryDetailQuery';
import { getCategoryProductsQuery } from 'app/_queries/getCategoryProductsQuery';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeCategoryProductsQuery } from 'graphql/requests/products/queries/CategoryProductsQuery.ssr';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { getMappedProductFilter } from 'utils/filterOptions/getMappedProductFilter';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';

type CategoryPageProps = {
    params: Promise<{
        categorySlug: string;
    }>;
    searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
};

const CategoryDetailPage = async ({ params, searchParams }: CategoryPageProps) => {
    const { categorySlug } = await params;
    const resolvedSearchParams = await searchParams;

    const currentPage =
        typeof resolvedSearchParams[PAGE_QUERY_PARAMETER_NAME] === 'string'
            ? Number(resolvedSearchParams[PAGE_QUERY_PARAMETER_NAME])
            : 1;

    const currentLoadMore =
        typeof resolvedSearchParams[LOAD_MORE_QUERY_PARAMETER_NAME] === 'string'
            ? Number(resolvedSearchParams[LOAD_MORE_QUERY_PARAMETER_NAME])
            : 0;

    const sort =
        typeof resolvedSearchParams[SORT_QUERY_PARAMETER_NAME] === 'string'
            ? (resolvedSearchParams[SORT_QUERY_PARAMETER_NAME] as TypeProductOrderingModeEnum)
            : TypeProductOrderingModeEnum.Priority;

    const filter = getMappedProductFilter(resolvedSearchParams[FILTER_QUERY_PARAMETER_NAME]) || undefined;

    const categoryData = await getCategoryDetailQuery(categorySlug, sort, filter);

    if (!categoryData) {
        return notFound();
    }

    const endCursors: string[] = [];

    let lastPromise: Promise<TypeCategoryProductsQuery | undefined> | null = null;

    for (let i = 0; i < currentLoadMore + 1; i++) {
        endCursors.push(getEndCursor(currentPage, i, DEFAULT_PAGE_SIZE));
        lastPromise = getCategoryProductsQuery(
            categorySlug,
            getEndCursor(currentPage, i, DEFAULT_PAGE_SIZE),
            sort,
            filter,
            DEFAULT_PAGE_SIZE,
        );
    }

    // {(!!currentFilter || !!currentSort) && <MetaRobots content="noindex, follow" />}
    // const firstImageUrl = categoryData?.images[0]?.url; // for OG tags
    // useHandleDefaultFiltersUpdate(categoryData?.products);
    // const seoTitle = useSeoTitleWithPagination(
    //     categoryData.products.totalCount,
    //     categoryData.name,
    //     categoryData.seoTitle,
    // );
    const title = categoryData.name;

    return (
        <VerticalStack gap="md">
            <CollapsibleDescriptionWithImage
                currentPage={currentPage}
                description={categoryData.description}
                imageName={categoryData.images[0]?.name || categoryData.name}
                imageUrl={categoryData.images[0]?.url}
                title={title}
            />

            <SimpleNavigation isWithoutSlider linkTypeOverride="category" listedItems={categoryData.children} />

            <FilteredProductsWrapper>
                <FilterPanelSection
                    categoryAutomatedFilters={categoryData.automatedFilters}
                    productFilterOptions={categoryData.products.productFilterOptions}
                    totalCount={categoryData.products.totalCount}
                />

                <div className="flex flex-1 flex-col gap-5">
                    <CategoryBestsellers products={categoryData.bestsellers} />

                    <div className="vl:flex-col flex flex-col-reverse">
                        <FilterSelectedParameters filterOptions={categoryData.products.productFilterOptions} />

                        <FilterAndSortingBarWrapper
                            sorting={categoryData.products.orderingMode}
                            totalCount={categoryData.products.totalCount}
                        />
                    </div>

                    <CategoryDetailContent categoryDetail={categoryData}>
                        {endCursors.map((endCursor) => (
                            <Suspense
                                key={`batch:${endCursor}`}
                                fallback={
                                    <div className={productListTwClass}>
                                        {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, skeletonIndex) => (
                                            <SkeletonModuleProductListItem
                                                key={`batch:${endCursor}-skeleton:${skeletonIndex}`}
                                            />
                                        ))}
                                    </div>
                                }
                            >
                                <CategoryDetailProductsServer
                                    categorySlug={categorySlug}
                                    endCursor={endCursor}
                                    filter={filter}
                                    orderingMode={sort}
                                />
                            </Suspense>
                        ))}

                        <CategoryDetailPagination
                            categoryDetailTotalCount={categoryData.products.totalCount}
                            hasNextPage={(await lastPromise)?.products.pageInfo.hasNextPage}
                        />
                    </CategoryDetailContent>
                </div>
            </FilteredProductsWrapper>

            {!!categoryData.readyCategorySeoMixLinks.length && (
                <AdvancedSeoCategories readyCategorySeoMixLinks={categoryData.readyCategorySeoMixLinks} />
            )}

            <LastVisitedProducts />
        </VerticalStack>
    );
};

export default CategoryDetailPage;

// const CategoryDetailPage: NextPage<ServerSidePropsType> = () => {
//     const currentFilter = useCurrentFilterQuery();
//     const currentSort = useCurrentSortQuery();
//     const { categoryData, isFetchingVisible } = useCategoryDetailData(currentFilter);

//     useHandleDefaultFiltersUpdate(categoryData?.products);
//     const seoTitle = useSeoTitleWithPagination(
//         categoryData?.products.totalCount,
//         categoryData?.name,
//         categoryData?.seoTitle,
//     );

//     const firstImageUrl = categoryData?.images[0]?.url;

//     return (
//         <PageDefer>
//             {(!!currentFilter || !!currentSort) && <MetaRobots content="noindex, follow" />}

//             <CommonLayout
//                 breadcrumbs={categoryData?.breadcrumb}
//                 breadcrumbsType="category"
//                 description={categoryData?.seoMetaDescription}
//                 hreflangLinks={categoryData?.hreflangLinks}
//                 isFetchingData={isFetchingVisible}
//                 ogImageUrlDefault={firstImageUrl}
//                 title={seoTitle}
//             >
//                 {!!categoryData && (
//                     <CategoryDetailContent category={categoryData} isFetchingVisible={isFetchingVisible} />
//                 )}
//             </CommonLayout>
//         </PageDefer>
//     );
// };

// export const getServerSideProps = getServerSidePropsWrapper(
//     ({ redisClient, domainConfig, ssrExchange, t }) =>
//         async (context) => {
//             const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);
//             const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 0);
//             const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers);
//             const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

//             if (redirect) {
//                 return redirect;
//             }

//             const client = await createClient({
//                 publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
//                 ssrExchange,
//                 redisClient,
//                 context,
//                 t,
//             });

//             let categoryUuid: string | null = null;

//             const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
//             const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
//             const categoryDetailResponsePromise = client!
//                 .query<TypeCategoryDetailQuery, TypeCategoryDetailQueryVariables>(CategoryDetailQueryDocument, {
//                     urlSlug,
//                     filter,
//                     orderingMode,
//                 })
//                 .toPromise();

//             const categoryProductsResponsePromise = client!
//                 .query(CategoryProductsQueryDocument, {
//                     endCursor: getEndCursor(page),
//                     orderingMode,
//                     filter,
//                     urlSlug,
//                     pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
//                 })
//                 .toPromise();

//             const [categoryDetailResponse] = await Promise.all([
//                 categoryDetailResponsePromise,
//                 categoryProductsResponsePromise,
//             ]);

//             categoryUuid = categoryDetailResponse.data?.category?.uuid || null;

//             if (getIsRedirectedFromSsr(context.req.headers)) {
//                 const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
//                     categoryDetailResponse.error,
//                     categoryDetailResponse.data?.category,
//                     context.res,
//                     domainConfig.url,
//                     urlSlug,
//                 );

//                 if (serverSideErrorResponse) {
//                     return serverSideErrorResponse;
//                 }
//             }

//             const initServerSideData = await initServerSideProps<TypeAdvertsQueryVariables>({
//                 domainConfig,
//                 context,
//                 client,
//                 ssrExchange,
//                 prefetchedQueries: [
//                     {
//                         query: AdvertsQueryDocument,
//                         variables: {
//                             positionNames: ['productListSecondRow'],
//                             categoryUuid,
//                         },
//                     },
//                 ],
//             });

//             return initServerSideData;
//         },
// );

// export default CategoryDetailPage;
