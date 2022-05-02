import {
    OrderSummaryContent,
    SummaryPrice,
    SummaryRow,
    SummaryTextAndImage,
    SummaryWrapper,
} from './OrderSummary.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { PriceType } from 'types/price';
import { formatPrice } from 'utils/formatting';

type PromoCodeProps = {
    promoCode: string;
    discount: PriceType;
};

const PromoCode: FC<PromoCodeProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-promocode';

    const t = useTypedTranslationFunction();

    return (
        <SummaryWrapper data-testid={testIdentifier}>
            <OrderSummaryContent>
                <SummaryRow>
                    <SummaryTextAndImage data-testid={testIdentifier + '-transport-name'}>
                        {`${t('Promo code')}: ${props.promoCode}`}
                    </SummaryTextAndImage>
                    <SummaryPrice data-testid={testIdentifier + '-transport-price'}>
                        <strong>-{formatPrice(props.discount.priceWithVat, props.discount.currencyCode, t)}</strong>
                    </SummaryPrice>
                </SummaryRow>
            </OrderSummaryContent>
        </SummaryWrapper>
    );
};

export default PromoCode;
