import {
    OrderSummaryContentStyled,
    OrderSummaryTotalPriceAmountStyled,
    OrderSummaryTotalPriceTextStyled,
    OrderSummaryTotalPriceWrapperStyled,
    PriceWrapperStyled,
} from './OrderSummary.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { PriceType } from 'types/price';
import { formatPrice } from 'utils/formatting';

type TotalPriceProps = {
    totalPrice: PriceType;
};

const TotalPrice: FC<TotalPriceProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-totalprice';

    const t = useTypedTranslationFunction();

    return (
        <OrderSummaryTotalPriceWrapperStyled data-testid={testIdentifier}>
            <OrderSummaryContentStyled>
                <PriceWrapperStyled>
                    <OrderSummaryTotalPriceTextStyled>{t('Total price')}</OrderSummaryTotalPriceTextStyled>
                    <OrderSummaryTotalPriceAmountStyled data-testid={testIdentifier + '-amount'}>
                        {formatPrice(props.totalPrice.priceWithVat, props.totalPrice.currencyCode, t)}
                    </OrderSummaryTotalPriceAmountStyled>
                </PriceWrapperStyled>
            </OrderSummaryContentStyled>
        </OrderSummaryTotalPriceWrapperStyled>
    );
};

export default TotalPrice;
