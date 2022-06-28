import {
    OrderSummaryContentStyled,
    OrderSummaryContentWrapperStyled,
    OrderSummaryTitleStyled,
    OrderSummaryWrapperStyled,
} from './OrderSummary.style';
import ProductsPreview from './ProductsPreview';
import PromoCode from './PromoCode';
import TotalPrice from './TotalPrice';
import TransportAndPayment from './TransportAndPayment';
import Adverts from 'components/Blocks/Adverts';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';

const OrderSummary: FC = () => {
    const testIdentifier = 'blocks-ordersummary';

    const t = useTypedTranslationFunction();
    const { cart, transport, payment, promoCode } = useCurrentCart(true);

    if (cart === null) {
        return null;
    }

    return (
        <>
            <Adverts positionName="cartPreview" withGapBottom />
            <OrderSummaryWrapperStyled data-testid={testIdentifier}>
                <OrderSummaryTitleStyled>{t('Your order')}</OrderSummaryTitleStyled>
                <OrderSummaryContentWrapperStyled>
                    <OrderSummaryContentStyled>
                        <ProductsPreview cartItems={cart.items} />
                        {(transport !== null || payment !== null) && (
                            <TransportAndPayment transport={transport} payment={payment} />
                        )}
                        {promoCode !== null && <PromoCode promoCode={promoCode} discount={cart.totalDiscountPrice} />}
                        <TotalPrice totalPrice={cart.totalPrice} />
                    </OrderSummaryContentStyled>
                </OrderSummaryContentWrapperStyled>
            </OrderSummaryWrapperStyled>
        </>
    );
};

export default OrderSummary;
