import {
    OrderSummaryContent,
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { PriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type PromoCodeProps = {
    promoCode: string;
    discount: PriceFragment;
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
