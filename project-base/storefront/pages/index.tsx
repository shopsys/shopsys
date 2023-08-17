import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Heading } from 'components/Basic/Heading/Heading';
import { Banners } from 'components/Blocks/Banners/Banners';
import { BLOG_PREVIEW_VARIABLES, BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'gtm/types/enums';
import { BlogArticlesQueryDocumentApi } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { PromotedCategoriesQueryDocumentApi } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { PromotedProductsQueryDocumentApi } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { SliderItemsQueryDocumentApi } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';

const HomePage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();

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
                    <Heading type="h2">{t('Promoted categories')}</Heading>
                    <PromotedCategories />
                </Webline>
                <Webline className="mb-6">
                    <Heading type="h2">{t('Promoted products')}</Heading>
                    <PromotedProducts />
                </Webline>
                <Webline type="blog">
                    <BlogPreview />
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                redisClient,
                domainConfig,
                prefetchedQueries: [
                    { query: PromotedCategoriesQueryDocumentApi },
                    { query: SliderItemsQueryDocumentApi },
                    { query: PromotedProductsQueryDocumentApi },
                    { query: BlogArticlesQueryDocumentApi, variables: BLOG_PREVIEW_VARIABLES },
                ],
                t,
            }),
);

export default HomePage;
