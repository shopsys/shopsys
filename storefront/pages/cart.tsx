import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CartSummary from 'components/Pages/Cart/CartSummary';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import List from 'components/Pages/Cart/List';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useShopsysSelector } from 'redux/store';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Cart: FC<ServerSidePropsType> = (props) => {
    const t = useTypedTranslationFunction();
    useInitDomainConfig(props.domainConfig);
    const cart = useShopsysSelector((state) => state.user.cart);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={1} domainUrl={props.domainConfig.url} />
                <List items={cart?.items} />
                <CartSummary />
                <OrderAction
                    activeStep={1}
                    buttonBack={t('Back to e-shop')}
                    buttonNext={t('Shipment and payment')}
                    isDisabled={false}
                />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default Cart;
