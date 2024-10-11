import {
    OrderSummaryContent,
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { TypePrice } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type PromoCodeProps = {
    promoCode: string;
    discount: TypePrice;
};

export const PromoCode: FC<PromoCodeProps> = ({ discount, promoCode }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper>
            <OrderSummaryContent>
                <OrderSummaryRow>
                    <OrderSummaryTextAndImage>{`${t('Promo code')}: ${promoCode}`}</OrderSummaryTextAndImage>
                    {isPriceVisible(discount.priceWithVat) && (
                        <OrderSummaryPrice>
                            <strong>-{formatPrice(discount.priceWithVat)}</strong>
                        </OrderSummaryPrice>
                    )}
                </OrderSummaryRow>
            </OrderSummaryContent>
        </OrderSummaryRowWrapper>
    );
};
