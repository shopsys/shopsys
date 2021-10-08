import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import Banners from 'components/Blocks/Banners';
import BlogPreview from 'components/Blocks/BlogPreview';
import { blogPreviewQuery } from 'connectors/blogPreview/blogPreview';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { navigationQuery } from 'connectors/navigation/Navigation';
import { nextReduxWrapper } from 'redux/main';
import PromotedCategories from 'components/Blocks/Categories/PromotedCategories/PromotedCategories';
import { promotedCategoriesQuery } from 'connectors/categories/PromotedCategories';
import PromotedProducts from 'components/Blocks/Product/PromotedProducts/PromotedProducts';
import { promotedProductsQuery } from 'connectors/products/Products';
import { sliderItemsQuery } from 'connectors/sliderItems/SliderItems';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Index: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
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
    return initServerSideProps(context, store, [
        promotedCategoriesQuery,
        sliderItemsQuery,
        promotedProductsQuery,
        navigationQuery,
        blogPreviewQuery,
    ]);
});

export default Index;
