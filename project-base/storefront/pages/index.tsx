import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { BLOG_PREVIEW_VARIABLES, BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    BlogArticlesQueryDocumentApi,
    BlogArticlesQueryVariablesApi,
    BlogUrlQueryDocumentApi,
    PromotedCategoriesQueryDocumentApi,
    PromotedProductsQueryDocumentApi,
    RecommendationTypeApi,
    RecommendedProductsQueryDocumentApi,
    RecommendedProductsQueryVariablesApi,
    SliderItemsQueryDocumentApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';

const RecommendedProducts = dynamic(() =>
    import('components/Blocks/Product/RecommendedProducts').then((component) => component.RecommendedProducts),
);

const HomePage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { isLuigisBoxActive } = useDomainConfig();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.homepage);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
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
                    <RecommendedProducts
                        recommendationType={RecommendationTypeApi.PersonalizedApi}
                        render={(recommendedProductsContent) => (
                            <Webline className="mb-6">
                                <h2 className="mb-3">{t('Recommended for you')}</h2> {recommendedProductsContent}
                            </Webline>
                        )}
                    />
                )}

                <Webline className="mb-6">
                    <h2 className="mb-3">{t('Promoted products')}</h2>
                    <PromotedProducts />
                </Webline>

                <Webline type="blog">
                    <BlogPreview />
                </Webline>

                <LastVisitedProducts />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, cookiesStoreState }) =>
        async (context) =>
            initServerSideProps<BlogArticlesQueryVariablesApi | RecommendedProductsQueryVariablesApi>({
                context,
                redisClient,
                domainConfig,
                prefetchedQueries: [
                    { query: PromotedCategoriesQueryDocumentApi },
                    { query: SliderItemsQueryDocumentApi },
                    { query: PromotedProductsQueryDocumentApi },
                    { query: BlogArticlesQueryDocumentApi, variables: BLOG_PREVIEW_VARIABLES },
                    { query: BlogUrlQueryDocumentApi },
                    ...(domainConfig.isLuigisBoxActive
                        ? [
                              {
                                  query: RecommendedProductsQueryDocumentApi,
                                  variables: {
                                      itemUuids: [],
                                      userIdentifier: cookiesStoreState.userIdentifier,
                                      recommendationType: RecommendationTypeApi.PersonalizedApi,
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
