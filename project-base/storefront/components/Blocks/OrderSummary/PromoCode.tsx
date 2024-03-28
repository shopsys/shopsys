import {
    OrderSummaryContent,
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type PromoCodeProps = {
    promoCode: string;
    discount: TypePriceFragment;
};

export const PromoCode: FC<PromoCodeProps> = ({ discount, promoCode }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper>
            <OrderSummaryContent>
                <OrderSummaryRow>
                    <OrderSummaryTextAndImage>{`${t('Promo code')}: ${promoCode}`}</OrderSummaryTextAndImage>
                    <OrderSummaryPrice>
                        <strong>-{formatPrice(discount.priceWithVat)}</strong>
                    </OrderSummaryPrice>
                </OrderSummaryRow>
            </OrderSummaryContent>
        </OrderSummaryRowWrapper>
    );
};
