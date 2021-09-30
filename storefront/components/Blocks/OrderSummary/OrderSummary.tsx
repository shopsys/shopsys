import {
    OrderSummaryContentStyled,
    OrderSummaryContentWrapperStyled,
    OrderSummaryTitle,
    OrderSummaryWrapperStyled,
} from './OrderSummary.style';
import { CartType } from 'connectors/cart/types';
import { FC } from 'react';
import ProductsPreview from './ProductsPreview';
import ShipmentAndPayment from './ShipmentAndPayment';
import TotalPrice from './TotalPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type OrderSummaryProps = {
    cart: CartType;
};

const OrderSummary: FC<OrderSummaryProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <Webline>
            <OrderSummaryWrapperStyled>
                <OrderSummaryTitle>{t('Your order')}</OrderSummaryTitle>
                <OrderSummaryContentWrapperStyled>
                    <OrderSummaryContentStyled>
                        <ProductsPreview cartItems={props.cart.items} />
                        <ShipmentAndPayment />
                        <TotalPrice />
                    </OrderSummaryContentStyled>
                </OrderSummaryContentWrapperStyled>
            </OrderSummaryWrapperStyled>
        </Webline>
    );
};

export default OrderSummary;
