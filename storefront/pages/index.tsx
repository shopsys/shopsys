import { initServerSideProps, ServerSidePropsType } from '../helpers/InitServerSideProps';
import Banners from 'components/Blocks/Banners';
import CommonLayout from '../components/Layout/CommonLayout';
import { domainActions } from '../redux/store/DomainStore';
import { FC } from 'react';
import { getDomainConfig } from '../utils/Domain/Domain';
import { GetServerSideProps } from 'next';
import Heading from '../components/Basic/Heading';
import { navigationQuery } from '../connectors/navigation/Navigation';
import PromotedCategories from '../components/Blocks/Categories/PromotedCategories/PromotedCategories';
import { promotedCategoriesQuery } from '../connectors/categories/PromotedCategories';
import PromotedProducts from '../components/Blocks/Product/PromotedProducts/PromotedProducts';
import { promotedProductsQuery } from '../connectors/products/Products';
import { sliderItemsQuery } from 'connectors/sliderItems/SliderItems';
import { useShopsysDispatch } from '../redux/store';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Index: FC<ServerSidePropsType> = (props) => {
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    let selectedDomain;
    if (props.domainConfig !== null) {
        selectedDomain = new URL(props.domainConfig?.url as string).host;
    }
    dispatch(domainActions.setDomain(getDomainConfig(selectedDomain)));

    return (
        <CommonLayout {...props}>
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
