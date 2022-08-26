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

const TEST_IDENTIFIER = 'blocks-ordersummary-totalprice';

export const TotalPrice: FC<TotalPriceProps> = ({ totalPrice }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryTotalPriceWrapperStyled data-testid={TEST_IDENTIFIER}>
            <OrderSummaryContentStyled>
                <PriceWrapperStyled>
                    <OrderSummaryTotalPriceTextStyled>{t('Total price')}</OrderSummaryTotalPriceTextStyled>
                    <OrderSummaryTotalPriceAmountStyled data-testid={TEST_IDENTIFIER + '-amount'}>
                        {formatPrice(totalPrice.priceWithVat)}
                    </OrderSummaryTotalPriceAmountStyled>
                </PriceWrapperStyled>
            </OrderSummaryContentStyled>
        </OrderSummaryTotalPriceWrapperStyled>
    );
};
