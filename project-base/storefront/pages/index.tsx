import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Heading } from 'components/Basic/Heading/Heading';
import { Banners } from 'components/Blocks/Banners/Banners';
import { BLOG_PREVIEW_VARIABLES, BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    BlogArticlesQueryDocumentApi,
    PromotedCategoriesQueryDocumentApi,
    PromotedProductsQueryDocumentApi,
    SliderItemsQueryDocumentApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'types/gtm/enums';

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

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) =>
        initServerSideProps({
            context,
            redisClient,
            prefetchedQueries: [
                { query: PromotedCategoriesQueryDocumentApi },
                { query: SliderItemsQueryDocumentApi },
                { query: PromotedProductsQueryDocumentApi },
                { query: BlogArticlesQueryDocumentApi, variables: BLOG_PREVIEW_VARIABLES },
            ],
        }),
);

export default HomePage;
