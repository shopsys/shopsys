import {
    OrderSummaryContentStyled,
    OrderSummaryContentWrapperStyled,
    OrderSummaryTitle,
    OrderSummaryWrapperStyled,
} from './OrderSummary.style';
import { FC } from 'react';
import ProductsPreview from './ProductsPreview';
import ShipmentAndPayment from './ShipmentAndPayment';
import TotalPrice from './TotalPrice';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const OrderSummary: FC = () => {
    const t = useTypedTranslationFunction();
    const { cart } = useShopsysSelector((state) => state.user);

    if (cart === null) {
        return null;
    }

    return (
        <OrderSummaryWrapperStyled>
            <OrderSummaryTitle>{t('Your order')}</OrderSummaryTitle>
            <OrderSummaryContentWrapperStyled>
                <OrderSummaryContentStyled>
                    <ProductsPreview cartItems={cart.items} />
                    <ShipmentAndPayment />
                    <TotalPrice />
                </OrderSummaryContentStyled>
            </OrderSummaryContentWrapperStyled>
        </OrderSummaryWrapperStyled>
    );
};

export default OrderSummary;
