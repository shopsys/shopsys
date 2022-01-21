import {
    OrderSummaryContentStyled,
    OrderSummaryContentWrapperStyled,
    OrderSummaryTitle,
    OrderSummaryWrapperStyled,
} from './OrderSummary.style';
import { FC } from 'react';
import ProductsPreview from './ProductsPreview';
import TotalPrice from './TotalPrice';
import TransportAndPayment from './TransportAndPayment';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const OrderSummary: FC = () => {
    const testIdentifier = 'blocks-ordersummary';

    const t = useTypedTranslationFunction();
    const { cart, transport, payment } = useShopsysSelector((state) => state.cart);

    if (cart === null) {
        return null;
    }

    return (
        <OrderSummaryWrapperStyled data-testid={testIdentifier}>
            <OrderSummaryTitle>{t('Your order')}</OrderSummaryTitle>
            <OrderSummaryContentWrapperStyled>
                <OrderSummaryContentStyled>
                    <ProductsPreview cartItems={cart.items} />
                    <TransportAndPayment transport={transport} payment={payment} />
                    <TotalPrice totalPrice={cart.totalPrice} />
                </OrderSummaryContentStyled>
            </OrderSummaryContentWrapperStyled>
        </OrderSummaryWrapperStyled>
    );
};

export default OrderSummary;
