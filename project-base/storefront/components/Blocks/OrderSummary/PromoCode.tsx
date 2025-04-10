import {
    OrderSummaryContent,
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { TypePromoCode } from 'graphql/types';
import { TypePrice } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type PromoCodeProps = {
    promoCode: TypePromoCode;
    discount: TypePrice;
};

export const PromoCode: FC<PromoCodeProps> = ({ discount, promoCode }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper>
            <OrderSummaryContent>
                <OrderSummaryRow>
                    <OrderSummaryTextAndImage>{`${t('Promo code')}: ${promoCode.code}`}</OrderSummaryTextAndImage>
                    {isPriceVisible(discount.priceWithVat) && Number(discount.priceWithVat) > 0 && (
                        <OrderSummaryPrice>
                            <strong>-{formatPrice(discount.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    )}
                </OrderSummaryRow>
            </OrderSummaryContent>
        </OrderSummaryRowWrapper>
    );
};
