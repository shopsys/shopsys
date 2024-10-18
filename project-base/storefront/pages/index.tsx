import { PageDefer } from 'components/Layout/PageDefer';
import { HomePageContent } from 'components/Pages/HomePage/HomePageContent';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import {
    TypeBlogArticlesQueryVariables,
    BlogArticlesQueryDocument,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { PromotedCategoriesQueryDocument } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { PromotedProductsQueryDocument } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import {
    RecommendedProductsQueryDocument,
    TypeRecommendedProductsQueryVariables,
} from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { SliderItemsQueryDocument } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { NextPage } from 'next';
import { getRecommenderClientIdentifier } from 'utils/recommender/getRecommenderClientIdentifier';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

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
                    ...(domainConfig.isLuigisBoxActive
                        ? [
                              {
                                  query: RecommendedProductsQueryDocument,
                                  variables: {
                                      itemUuids: [],
                                      userIdentifier: cookiesStoreState.userIdentifier,
                                      recommendationType: TypeRecommendationType.Personalized,
                                      recommenderClientIdentifier: getRecommenderClientIdentifier(context.resolvedUrl),
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
