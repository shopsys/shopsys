import {
    OrderSummaryContentStyled,
    OrderSummaryPriceStyled,
    OrderSummaryRowStyled,
    OrderSummaryRowWrapperStyled,
    OrderSummaryTextAndImageStyled,
} from './OrderSummary.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { PriceType } from 'types/price';

type PromoCodeProps = {
    promoCode: string;
    discount: PriceType;
};

export const PromoCode: FC<PromoCodeProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-promocode';

    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapperStyled data-testid={testIdentifier}>
            <OrderSummaryContentStyled>
                <OrderSummaryRowStyled>
                    <OrderSummaryTextAndImageStyled data-testid={testIdentifier + '-promocode-name'}>
                        {`${t('Promo code')}: ${props.promoCode}`}
                    </OrderSummaryTextAndImageStyled>
                    <OrderSummaryPriceStyled data-testid={testIdentifier + '-promocode-discount'}>
                        <strong>-{formatPrice(props.discount.priceWithVat)}</strong>
                    </OrderSummaryPriceStyled>
                </OrderSummaryRowStyled>
            </OrderSummaryContentStyled>
        </OrderSummaryRowWrapperStyled>
    );
};
