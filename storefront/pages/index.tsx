import { initServerSideProps, ServerSidePropsType } from '../helpers/InitServerSideProps';
import Banners from 'components/Blocks/Banners';
import CommonLayout from '../components/Layout/CommonLayout';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import Heading from '../components/Basic/Heading';
import { navigationQuery } from '../connectors/navigation/Navigation';
import PromotedCategories from '../components/Blocks/Categories/PromotedCategories/PromotedCategories';
import { promotedCategoriesQuery } from '../connectors/categories/PromotedCategories';
import PromotedProducts from '../components/Blocks/Product/PromotedProducts/PromotedProducts';
import { promotedProductsQuery } from '../connectors/products/Products';
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
        </CommonLayout>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [
        promotedCategoriesQuery,
        sliderItemsQuery,
        promotedProductsQuery,
        navigationQuery,
    ]);
};

export default Index;
