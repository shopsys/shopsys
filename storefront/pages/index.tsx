import { DomainConfigType, getDomainConfig } from '../utils/Domain/Domain';
import Banners from 'components/Blocks/Banners';
import CommonLayout from '../components/Layout/CommonLayout';
import { domainActions } from '../redux/store/DomainStore';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import { navigationQuery } from '../connectors/navigation/Navigation';
import PromotedCategories from '../components/Blocks/Categories/PromotedCategories/PromotedCategories';
import { promotedCategoriesQuery } from '../connectors/categories/PromotedCategories';
import PromotedProducts from '../components/Blocks/Product/PromotedProducts/PromotedProducts';
import { promotedProductsQuery } from '../connectors/products/Products';
import ShopsysHeading from '../components/Basic/ShopsysHeading';
import { sliderItemsQuery } from 'connectors/sliderItems/SliderItems';
import { useShopsysDispatch } from '../redux/store';
import { useTranslation } from 'react-i18next';
import Webline from 'components/Layout/Webline';

type IndexProps = { domainConfig?: DomainConfigType };

const Index: FC<IndexProps> = (props) => {
    const dispatch = useShopsysDispatch();
    const { t } = useTranslation();

    let selectedDomain;
    if (props.domainConfig !== null) {
        selectedDomain = new URL(props.domainConfig?.url as string).host;
    }
    dispatch(domainActions.setDomain(getDomainConfig(selectedDomain)));

    return (
        <CommonLayout>
            <Webline>
                <Banners />
            </Webline>
            <Webline>
                <ShopsysHeading type="h2">{t<string>('Promoted categories')}</ShopsysHeading>
                <PromotedCategories />
            </Webline>
            <Webline>
                <ShopsysHeading type="h2">{t<string>('Promoted products')}</ShopsysHeading>
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
