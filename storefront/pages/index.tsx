import { SearchMetadata } from 'components/Basic/Head/SearchMetadata/SearchMetadata';
import { Heading } from 'components/Basic/Heading/Heading';
import { Banners } from 'components/Blocks/Banners/Banners';
import { BlogPreview } from 'components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories/PromotedCategories';
import { PromotedProducts } from 'components/Blocks/Product/PromotedProducts/PromotedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { blogPreviewVariables } from 'connectors/articleInterface/blogArticle/BlogArticle';
import {
    BlogArticlesQueryDocumentApi,
    PromotedCategoriesQueryDocumentApi,
    PromotedProductsQueryDocumentApi,
    SliderItemsQueryDocumentApi,
} from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const HomePage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('home');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <>
            <SearchMetadata />
            <CommonLayout>
                <Webline>
                    <Banners />
                </Webline>
                <Webline>
                    <Heading type="h2">{t('Promoted categories')}</Heading>
                    <PromotedCategories />
                </Webline>
                <Webline>
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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, false, [
        { query: PromotedCategoriesQueryDocumentApi },
        { query: SliderItemsQueryDocumentApi },
        { query: PromotedProductsQueryDocumentApi },
        { query: BlogArticlesQueryDocumentApi, variables: blogPreviewVariables },
    ]);
});

export default HomePage;
