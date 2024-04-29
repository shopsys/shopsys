import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { DeferredBlogPreview } from 'components/Blocks/BlogPreview/DeferredBlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { DeferredPromotedProducts } from 'components/Blocks/Product/DeferredPromotedProducts';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageDefer } from 'components/Layout/PageDefer';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import {
    TypeBlogArticlesQueryVariables,
    BlogArticlesQueryDocument,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { BlogUrlQueryDocument } from 'graphql/requests/blogCategories/queries/BlogUrlQuery.generated';
import { PromotedCategoriesQueryDocument } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { PromotedProductsQueryDocument } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import {
    RecommendedProductsQueryDocument,
    TypeRecommendedProductsQueryVariables,
} from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { SliderItemsQueryDocument } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const HomePage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { isLuigisBoxActive } = useDomainConfig();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.homepage);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <PageDefer>
            <SearchMetadata />
            <CommonLayout>
                <Webline className="mb-14">
                    <Banners />
                </Webline>

                <Webline className="mb-6">
                    <h2 className="mb-3">{t('Promoted categories')}</h2>
                    <PromotedCategories />
                </Webline>
                {isLuigisBoxActive && (
                    <DeferredRecommendedProducts
                        recommendationType={TypeRecommendationType.Personalized}
                        render={(recommendedProductsContent) => (
                            <Webline className="mb-6">
                                <h2 className="mb-3">{t('Recommended for you')}</h2> {recommendedProductsContent}
                            </Webline>
                        )}
                    />
                )}
                <Webline className="mb-6">
                    <h2 className="mb-3">{t('Promoted products')}</h2>
                    <DeferredPromotedProducts />
                </Webline>

                <Webline type="blog">
                    <DeferredBlogPreview />
                </Webline>

                <DeferredLastVisitedProducts />
            </CommonLayout>
        </PageDefer>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, cookiesStoreState }) =>
        async (context) =>
            initServerSideProps<TypeBlogArticlesQueryVariables | TypeRecommendedProductsQueryVariables>({
                context,
                redisClient,
                domainConfig,
                prefetchedQueries: [
                    { query: PromotedCategoriesQueryDocument },
                    { query: SliderItemsQueryDocument },
                    { query: PromotedProductsQueryDocument },
                    { query: BlogArticlesQueryDocument, variables: BLOG_PREVIEW_VARIABLES },
                    { query: BlogUrlQueryDocument },
                    ...(domainConfig.isLuigisBoxActive
                        ? [
                              {
                                  query: RecommendedProductsQueryDocument,
                                  variables: {
                                      itemUuids: [],
                                      userIdentifier: cookiesStoreState.userIdentifier,
                                      recommendationType: TypeRecommendationType.Personalized,
                                      limit: 10,
                                  },
                              },
                          ]
                        : []),
                ],
                t,
            }),
);

export default HomePage;
