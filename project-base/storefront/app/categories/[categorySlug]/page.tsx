import { ProductsList } from 'app/_components/Blocks/Product/ProductsList/ProductsList';
import { getCategoryDetailQuery } from 'app/_queries/getCategoryDetailQuery';
import { getCategoryProductsQuery } from 'app/_queries/getCategoryProductsQuery';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeProductFilter, TypeProductOrderingModeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { notFound } from 'next/navigation';

type CategoryPageProps = {
    params: Promise<{
        categorySlug: string;
        sort: TypeProductOrderingModeEnum;
        filter: TypeProductFilter;
    }>;
};

const CategoryDetailPage = async ({ params }: CategoryPageProps) => {
    const { categorySlug, sort, filter } = await params;

    const categoryData = await getCategoryDetailQuery(categorySlug, sort, filter);

    if (!categoryData) {
        return notFound();
    }

    console.log('🐳 categoryData', categoryData);

    const products = await getCategoryProductsQuery(categorySlug, '', sort, filter, 10);

    if (!products) {
        return notFound();
    }

    return (
        <Webline>
            <VerticalStack gap="md">
                <h1>category</h1>

                <ProductsList
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    gtmProductListName={GtmProductListNameType.category_detail}
                    products={products}
                />
            </VerticalStack>
        </Webline>
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
