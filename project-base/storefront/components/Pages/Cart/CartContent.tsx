import { CartList } from './CartList/CartList';
import { CartLoading } from './CartLoading';
import { CartSummary } from './CartSummary';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';

export const CartContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const { cart, isFetching, isCartHydrated } = useCurrentCart();

    if (!isCartHydrated || isFetching) {
        return <CartLoading />;
    }

    if (!cart || !cart.items.length) {
        return (
            <p className="my-28 text-center text-2xl" tid={TIDs.cart_page_empty_cart_text}>
                {t('Your cart is currently empty.')}
            </p>
        );
    }

    return (
        <>
            <OrderSteps activeStep={1} domainUrl={url} />

            <CartList items={cart.items} />

            <CartSummary />

            <OrderAction
                withGapBottom
                buttonBack={t('Back')}
                buttonBackLink="/"
                buttonNext={t('Transport and payment')}
                buttonNextLink={transportAndPaymentUrl}
                hasDisabledLook={false}
                withGapTop={false}
            />
        </>
    );
};
