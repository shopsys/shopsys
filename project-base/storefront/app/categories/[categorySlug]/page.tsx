import { CollapsibleDescriptionWithImage } from 'app/_components/Blocks/CollapsibleDescriptionWithImage/CollapsibleDescriptionWithImage';
import { FilteredProductsWrapper } from 'app/_components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { ProductsListWrapper } from 'app/_components/Blocks/Product/ProductsList/ProductsListWrapper';
import { SimpleNavigation } from 'app/_components/Blocks/SimpleNavigation/SimpleNavigation';
import { FilterAndSortingBarWrapper } from 'app/_components/Blocks/SortingBar/FilterAndSortingBarWrapper';
import { AdvancedSeoCategories } from 'app/_components/Page/CategoryDetail/AdvancedSeoCategories';
import { CategoryBestsellers } from 'app/_components/Page/CategoryDetail/CategoryBestsellers/CategoryBestsellers';
import { getCategoryDetailQuery } from 'app/_queries/getCategoryDetailQuery';
import { SkeletonModuleProductList } from 'components/Blocks/Skeleton/SkeletonModuleProductList';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { TypeProductFilter, TypeProductOrderingModeEnum } from 'graphql/types';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';

type CategoryPageProps = {
    params: Promise<{
        categorySlug: string;
    }>;
    searchParams: Promise<{
        sort: TypeProductOrderingModeEnum | undefined;
        filter: TypeProductFilter | undefined;
        page: number | undefined;
    }>;
};

const getOrderingMode = (sort: TypeProductOrderingModeEnum | undefined) => {
    return sort !== undefined &&
        Object.values(TypeProductOrderingModeEnum).includes(sort as TypeProductOrderingModeEnum)
        ? sort
        : TypeProductOrderingModeEnum.Priority;
};

const CategoryDetailPage = async ({ params, searchParams }: CategoryPageProps) => {
    const { categorySlug } = await params;
    const { sort, filter, page } = await searchParams;

    const orderingMode = getOrderingMode(sort);
    const currentPage = page ?? 1;

    const categoryData = await getCategoryDetailQuery(categorySlug, orderingMode, filter);

    if (!categoryData) {
        return notFound();
    }

    // const title = useSeoTitleWithPagination(categoryData.products.totalCount, categoryData.name, categoryData.seoH1);
    const title = categoryData.name;

    // console.log('🔀 sort', sort);
    // console.log('🔍 filter', filter);
    // console.log('📄 page', page);

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
                {/* <FilterPanelWrapper params={params} /> */}

                <div className="flex flex-1 flex-col gap-5">
                    <CategoryBestsellers products={categoryData.bestsellers} />

                    <div className="vl:flex-col flex flex-col-reverse">
                        {/* <FilterSelectedParameters filterOptions={category.products.productFilterOptions} /> */}

                        <FilterAndSortingBarWrapper
                            sorting={categoryData.products.orderingMode}
                            totalCount={categoryData.products.totalCount}
                        />
                    </div>

                    {/* <DeferredCategoryDetailProductsWrapper
                        category={category}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                    /> */}

                    <Suspense
                        key={`${categorySlug}-${sort}-${JSON.stringify(filter)}`}
                        fallback={<SkeletonModuleProductList />}
                    >
                        <ProductsListWrapper categorySlug={categorySlug} filter={filter} orderingMode={orderingMode} />
                    </Suspense>
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
