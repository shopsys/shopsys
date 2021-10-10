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
    const t = useTypedTranslationFunction();
    const { cart, transport, payment } = useShopsysSelector((state) => state.user);

    if (cart === null) {
        return null;
    }

    return (
        <OrderSummaryWrapperStyled>
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
