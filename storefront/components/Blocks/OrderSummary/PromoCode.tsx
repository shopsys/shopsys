import {
    OrderSummaryContent,
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { PriceType } from 'types/price';

type PromoCodeProps = {
    promoCode: string;
    discount: PriceType;
};

const TEST_IDENTIFIER = 'blocks-ordersummary-promocode';

export const PromoCode: FC<PromoCodeProps> = ({ discount, promoCode }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper data-testid={TEST_IDENTIFIER}>
            <OrderSummaryContent>
                <OrderSummaryRow>
                    <OrderSummaryTextAndImage data-testid={TEST_IDENTIFIER + '-promocode-name'}>
                        {`${t('Promo code')}: ${promoCode}`}
                    </OrderSummaryTextAndImage>
                    <OrderSummaryPrice data-testid={TEST_IDENTIFIER + '-promocode-discount'}>
                        <strong>-{formatPrice(discount.priceWithVat)}</strong>
                    </OrderSummaryPrice>
                </OrderSummaryRow>
            </OrderSummaryContent>
        </OrderSummaryRowWrapper>
    );
};
