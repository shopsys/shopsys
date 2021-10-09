import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CartSummary from 'components/Pages/Cart/CartSummary';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import List from 'components/Pages/Cart/List';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Cart: FC<ServerSidePropsType> = (props) => {
    const { cart } = useShopsysSelector((state) => state.user);
    const t = useTypedTranslationFunction();
    useInitDomainConfig(props.domainConfig);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={1} domainUrl={props.domainConfig.url} />
                <List items={cart?.items} />
                <CartSummary />
                <Webline>
                    <OrderAction
                        activeStep={1}
                        buttonBack={t('Back to e-shop')}
                        buttonNext={t('Shipment and payment')}
                        isDisabled={false}
                        withGapBottom={true}
                    />
                </Webline>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return initServerSideProps(context, store, [navigationQuery]);
});

export default Cart;
