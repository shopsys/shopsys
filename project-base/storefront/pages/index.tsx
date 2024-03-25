import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { BLOG_PREVIEW_VARIABLES, BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    BlogArticlesQueryDocument,
    BlogArticlesQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { BlogUrlQueryDocument } from 'graphql/requests/blogCategories/queries/BlogUrlQuery.generated';
import { PromotedCategoriesQueryDocument } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { PromotedProductsQueryDocument } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { SliderItemsQueryDocument } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';

const HomePage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();

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
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps<BlogArticlesQueryVariables>({
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
