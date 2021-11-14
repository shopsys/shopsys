import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import {
    NavigationQueryDocumentApi,
    PromotedCategoriesQueryDocumentApi,
    PromotedProductsQueryDocumentApi,
    SliderItemsQueryDocumentApi,
} from 'graphql/generated';
import Banners from 'components/Blocks/Banners';
import BlogPreview from 'components/Blocks/BlogPreview';
import { blogPreviewQuery } from 'connectors/blogPreview/blogPreview';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { nextReduxWrapper } from 'redux/main';
import PromotedCategories from 'components/Blocks/Categories/PromotedCategories/PromotedCategories';
import PromotedProducts from 'components/Blocks/Product/PromotedProducts/PromotedProducts';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

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
        { query: NavigationQueryDocumentApi },
        { query: blogPreviewQuery },
    ]);
});

export default Index;
