import {
    OrderSummaryContentStyled,
    OrderSummaryContentWrapperStyled,
    OrderSummaryTitleStyled,
    OrderSummaryWrapperStyled,
    TransportAndPaymentPreviewWrapperStyled,
} from './OrderSummary.style';
import { ProductsPreview } from './ProductsPreview';
import { PromoCode } from './PromoCode';
import { TotalPrice } from './TotalPrice';
import { TransportAndPayment } from './TransportAndPayment';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

type OrderSummaryProps = {
    isTransportOrPaymentLoading?: boolean;
};

const TEST_IDENTIFIER = 'blocks-ordersummary';

export const OrderSummary: FC<OrderSummaryProps> = ({ isTransportOrPaymentLoading }) => {
    const t = useTypedTranslationFunction();
    const { cart, transport, payment, promoCode } = useCurrentCart();

    if (cart === null) {
        return null;
    }

    return (
        <>
            <Adverts positionName="cartPreview" withGapBottom />
            <OrderSummaryWrapperStyled data-testid={TEST_IDENTIFIER}>
                <OrderSummaryTitleStyled>{t('Your order')}</OrderSummaryTitleStyled>
                <OrderSummaryContentWrapperStyled>
                    <OrderSummaryContentStyled>
                        <ProductsPreview cartItems={cart.items} />
                        <TransportAndPaymentPreviewWrapperStyled>
                            {isTransportOrPaymentLoading && (transport !== null || payment !== null) && (
                                <LoaderWithOverlay iconSize={30} />
                            )}
                            {(transport !== null || payment !== null) && (
                                <TransportAndPayment transport={transport} payment={payment} />
                            )}
                            {promoCode !== null && (
                                <PromoCode promoCode={promoCode} discount={cart.totalDiscountPrice} />
                            )}
                        </TransportAndPaymentPreviewWrapperStyled>
                        <TotalPrice totalPrice={cart.totalPrice} />
                    </OrderSummaryContentStyled>
                </OrderSummaryContentWrapperStyled>
            </OrderSummaryWrapperStyled>
        </>
    );
};
