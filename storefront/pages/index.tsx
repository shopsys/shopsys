import { DomainConfigType, getDomainConfig } from '../utils/Domain/Domain';
import Banners from 'components/blocks/banners';
import { domainActions } from '../redux/store/DomainStore';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import Header from '../components/layout/Header';
import { initServerSideProps } from '../helpers/InitServerSideProps';
import NewsletterForm from 'components/layout/Footer/NewsletterForm';
import PromotedCategories from '../components/blocks/categories/PromotedCategories/PromotedCategories';
import { promotedCategoriesQuery } from '../connectors/categories/PromotedCategories';
import PromotedProducts from '../components/blocks/product/PromotedProducts/PromotedProducts';
import { promotedProductsQuery } from '../connectors/products/Products';
import ShopsysHeading from '../components/basic/ShopsysHeading';
import { sliderItemsQuery } from 'connectors/sliderItems/SliderItems';
import { useShopsysDispatch } from '../redux/store';
import { useTranslation } from 'react-i18next';
import Webline from 'components/layout/Webline';

type IndexProps = { domainConfig?: DomainConfigType };

const Index: FC<IndexProps> = (props) => {
    const dispatch = useShopsysDispatch();
    const { t } = useTranslation();
    dispatch(domainActions.setDomain(getDomainConfig(props.domainConfig?.domain)));

    return (
        <>
            <Webline type="colored">
                <Header></Header>
            </Webline>
            <Webline>
                <Banners />
            </Webline>
            <Webline>
                <ShopsysHeading type="h2">{t('Promoted categories')}</ShopsysHeading>
                <PromotedCategories />
            </Webline>
            <Webline>
                <ShopsysHeading type="h2">{t('Promoted products')}</ShopsysHeading>
                <PromotedProducts />
            </Webline>
            <Webline type="light">
                <NewsletterForm />
            </Webline>
        </>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [promotedCategoriesQuery, sliderItemsQuery, promotedProductsQuery]);
};

export default Index;
