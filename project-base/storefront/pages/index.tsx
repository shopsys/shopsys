import { PageDefer } from 'components/Layout/PageDefer';
import { ExchangeTokenHandler } from 'components/Pages/HomePage/ExchangeTokenHandler';
import { HomePageContent } from 'components/Pages/HomePage/HomePageContent';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import {
    BlogArticlesQueryDocument,
    TypeBlogArticlesQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { PromotedCategoriesQueryDocument } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { PromotedProductsQueryDocument } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { SliderItemsQueryDocument } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
import { NextPage } from 'next';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const HomePage: NextPage<ServerSidePropsType> = () => {
    return (
        <PageDefer>
            <ExchangeTokenHandler />
            <HomePageContent />
        </PageDefer>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps<TypeBlogArticlesQueryVariables>({
                context,
                redisClient,
                domainConfig,
                prefetchedQueries: [
                    { query: PromotedCategoriesQueryDocument },
                    { query: SliderItemsQueryDocument },
                    { query: PromotedProductsQueryDocument },
                    { query: BlogArticlesQueryDocument, variables: BLOG_PREVIEW_VARIABLES },
                ],
                t,
            }),
);

export default HomePage;
