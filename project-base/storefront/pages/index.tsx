import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts';
// import { Banners } from 'components/Blocks/Banners/Banners';
// import { BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
// import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
// import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
// import { Webline } from 'components/Layout/Webline/Webline';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import {
    BlogArticlesQueryDocument,
    TypeBlogArticlesQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { BlogUrlQueryDocument } from 'graphql/requests/blogCategories/queries/BlogUrlQuery.generated';
import { PromotedCategoriesQueryDocument } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { PromotedProductsQueryDocument } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { SliderItemsQueryDocument } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';

// import useTranslation from 'next-translate/useTranslation';
// import dynamic from 'next/dynamic';

// const GtmEvents = dynamic(
//     () => import('components/Pages/HomePage/GtmEvents').then((component) => component.GtmEvents),
//     { ssr: false },
// );

// const LastVisitedProducts = dynamic(
//     () =>
//         import('components/Blocks/Product/LastVisitedProducts/LastVisitedProducts').then(
//             (component) => component.LastVisitedProducts,
//         ),
//     { ssr: false },
// );

const HomePage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    return (
        <>
            {/* <GtmEvents /> */}
            <SearchMetadata />
            <CommonLayout>
                <Webline className="mb-14">
                    <Banners />
                </Webline>

                <Webline className="mb-6">
                    <h2 className="mb-3">{t('Promoted categories')}</h2>
                    <PromotedCategories />
                </Webline>

                <Webline className="mb-6">
                    <h2 className="mb-3">{t('Promoted products')}</h2>
                    <PromotedProducts />
                </Webline>

                <Webline type="blog">
                    <BlogPreview />
                </Webline>
                {/* <LastVisitedProducts /> */}
            </CommonLayout>
        </>
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
                    { query: BlogUrlQueryDocument },
                ],
                t,
            }),
);

export default HomePage;
