import {
    OrderSummaryContent,
    OrderSummaryTotalPriceAmount,
    OrderSummaryTotalPriceText,
    OrderSummaryTotalPriceWrapper,
    PriceWrapper,
} from './OrderSummary.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { PriceType } from 'types/price';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type TotalPriceProps = {
    totalPrice: PriceType;
};

const TotalPrice: FC<TotalPriceProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-totalprice';

    const t = useTypedTranslationFunction();

    return (
        <OrderSummaryTotalPriceWrapper data-testid={testIdentifier}>
            <OrderSummaryContent>
                <PriceWrapper>
                    <OrderSummaryTotalPriceText>{t('Total price')}</OrderSummaryTotalPriceText>
                    <OrderSummaryTotalPriceAmount data-testid={testIdentifier + '-amount'}>
                        {formatPrice(props.totalPrice.priceWithVat, props.totalPrice.currencyCode, t)}
                    </OrderSummaryTotalPriceAmount>
                </PriceWrapper>
            </OrderSummaryContent>
        </OrderSummaryTotalPriceWrapper>
    );
};

export default TotalPrice;
