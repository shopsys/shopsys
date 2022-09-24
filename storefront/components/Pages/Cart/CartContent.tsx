import { CartList } from './CartList/CartList';
import { CartLoading } from './CartLoading/CartLoading';
import { CartSummary } from './CartSummary/CartSummary';
import { EmptyCart } from './EmptyCart/EmptyCart';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { Webline } from 'components/Layout/Webline/Webline';
import { useCurrentCart } from 'connectors/cart/Cart';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

export const CartContent: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);
    const t = useTypedTranslationFunction();
    const { loginLoading } = useShopsysSelector((state) => state.user);
    const { cart, isCartEmpty, isInitiallyLoaded } = useCurrentCart();

    return (
        <>
            {isInitiallyLoaded && loginLoading === 'not-loading' ? (
                <>
                    {isCartEmpty ? (
                        <EmptyCart />
                    ) : (
                        <>
                            <OrderSteps activeStep={1} domainUrl={domainUrl} />
                            <CartList items={cart?.items} />
                            <CartSummary />
                            <Webline>
                                <OrderAction
                                    buttonBack={t('Back')}
                                    buttonNext={t('Transport and payment')}
                                    hasDisabledLook={false}
                                    withGapTop={false}
                                    withGapBottom
                                    buttonBackLink="/"
                                    buttonNextLink={transportAndPaymentUrl}
                                />
                            </Webline>
                        </>
                    )}
                </>
            ) : (
                <CartLoading />
            )}
        </>
    );
};
