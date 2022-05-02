import {
    OrderSummaryContentStyled,
    OrderSummaryPriceStyled,
    OrderSummaryRowStyled,
    OrderSummaryRowWrapperStyled,
    OrderSummaryTextAndImageStyled,
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
        <OrderSummaryRowWrapperStyled data-testid={testIdentifier}>
            <OrderSummaryContentStyled>
                <OrderSummaryRowStyled>
                    <OrderSummaryTextAndImageStyled data-testid={testIdentifier + '-promocode-name'}>
                        {`${t('Promo code')}: ${props.promoCode}`}
                    </OrderSummaryTextAndImageStyled>
                    <OrderSummaryPriceStyled data-testid={testIdentifier + '-promocode-discount'}>
                        <strong>-{formatPrice(props.discount.priceWithVat, props.discount.currencyCode, t)}</strong>
                    </OrderSummaryPriceStyled>
                </OrderSummaryRowStyled>
            </OrderSummaryContentStyled>
        </OrderSummaryRowWrapperStyled>
    );
};

export default PromoCode;
