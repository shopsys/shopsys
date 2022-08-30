import {
    OrderSummaryContentStyled,
    OrderSummaryPriceStyled,
    OrderSummaryRowStyled,
    OrderSummaryRowWrapperStyled,
    OrderSummaryTextAndImageStyled,
} from './OrderSummary.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
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
        <OrderSummaryRowWrapperStyled data-testid={TEST_IDENTIFIER}>
            <OrderSummaryContentStyled>
                <OrderSummaryRowStyled>
                    <OrderSummaryTextAndImageStyled data-testid={TEST_IDENTIFIER + '-promocode-name'}>
                        {`${t('Promo code')}: ${promoCode}`}
                    </OrderSummaryTextAndImageStyled>
                    <OrderSummaryPriceStyled data-testid={TEST_IDENTIFIER + '-promocode-discount'}>
                        <strong>-{formatPrice(discount.priceWithVat)}</strong>
                    </OrderSummaryPriceStyled>
                </OrderSummaryRowStyled>
            </OrderSummaryContentStyled>
        </OrderSummaryRowWrapperStyled>
    );
};
