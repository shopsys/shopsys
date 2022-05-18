import Heading from 'components/Basic/Heading';
import Banners from 'components/Blocks/Banners';
import BlogPreview from 'components/Blocks/BlogPreview';
import PromotedCategories from 'components/Blocks/Categories/PromotedCategories/PromotedCategories';
import PromotedProducts from 'components/Blocks/Product/PromotedProducts/PromotedProducts';
import CommonLayout from 'components/Layout/CommonLayout';
import Webline from 'components/Layout/Webline';
import { blogPreviewVariables } from 'connectors/articleInterface/blogArticle/BlogArticle';
import {
    BlogArticlesQueryDocumentApi,
    PromotedCategoriesQueryDocumentApi,
    PromotedProductsQueryDocumentApi,
    SliderItemsQueryDocumentApi,
} from 'graphql/generated';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const Index: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();

    return (
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
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [
        { query: PromotedCategoriesQueryDocumentApi },
        { query: SliderItemsQueryDocumentApi },
        { query: PromotedProductsQueryDocumentApi },
        { query: BlogArticlesQueryDocumentApi, variables: blogPreviewVariables },
    ]);
});

export default Index;
