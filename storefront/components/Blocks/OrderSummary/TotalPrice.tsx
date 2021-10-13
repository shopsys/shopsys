import {
    OrderSummaryContent,
    OrderSummaryTotalPriceAmount,
    OrderSummaryTotalPriceText,
    OrderSummaryTotalPriceWrapper,
    PriceWrapper,
} from './OrderSummary.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const TotalPrice: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <OrderSummaryTotalPriceWrapper>
            <OrderSummaryContent>
                <PriceWrapper>
                    <OrderSummaryTotalPriceText>{t('Total price')}</OrderSummaryTotalPriceText>
                    {/* TODO PRG: total price is hard coded */}
                    <OrderSummaryTotalPriceAmount>{formatPrice(4040, 'CZK', t)}</OrderSummaryTotalPriceAmount>
                </PriceWrapper>
            </OrderSummaryContent>
        </OrderSummaryTotalPriceWrapper>
    );
};

export default TotalPrice;
