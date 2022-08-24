import {
    OrderSummaryContentStyled,
    OrderSummaryTotalPriceAmountStyled,
    OrderSummaryTotalPriceTextStyled,
    OrderSummaryTotalPriceWrapperStyled,
    PriceWrapperStyled,
} from './OrderSummary.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { PriceType } from 'types/price';

type TotalPriceProps = {
    totalPrice: PriceType;
};

export const TotalPrice: FC<TotalPriceProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-totalprice';

    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryTotalPriceWrapperStyled data-testid={testIdentifier}>
            <OrderSummaryContentStyled>
                <PriceWrapperStyled>
                    <OrderSummaryTotalPriceTextStyled>{t('Total price')}</OrderSummaryTotalPriceTextStyled>
                    <OrderSummaryTotalPriceAmountStyled data-testid={testIdentifier + '-amount'}>
                        {formatPrice(props.totalPrice.priceWithVat)}
                    </OrderSummaryTotalPriceAmountStyled>
                </PriceWrapperStyled>
            </OrderSummaryContentStyled>
        </OrderSummaryTotalPriceWrapperStyled>
    );
};
