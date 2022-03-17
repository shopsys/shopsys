import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CartSummary from 'components/Pages/Cart/CartSummary';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import List from 'components/Pages/Cart/List';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Cart: FC<ServerSidePropsType> = () => {
    const { cart } = useShopsysSelector((state) => state.cart);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <OrderSteps activeStep={1} domainUrl={domainUrl} />
                <List items={cart?.items} />
                <CartSummary />
                <Webline>
                    <OrderAction
                        activeStep={1}
                        buttonBack={t('Back')}
                        buttonNext={t('Transport and payment')}
                        hasDisabledLook={false}
                        withGapTop={false}
                        withGapBottom={true}
                        buttonBackLink="/"
                        buttonNextLink={transportAndPaymentUrl}
                    />
                </Webline>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Cart;
