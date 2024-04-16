import { PageDefer } from 'components/Layout/PageDefer';
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
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const HomePageContent = dynamic(() =>
    import('components/Pages/HomePage/HomePageContent').then((component) => component.HomePageContent),
);

const HomePage: NextPage<ServerSidePropsType> = () => {
    return (
        <PageDefer>
            <HomePageContent />
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
