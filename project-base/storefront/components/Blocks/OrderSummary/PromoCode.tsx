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
    code: string;
    discount: TypePrice;
};

export const PromoCode: FC<PromoCodeProps> = ({ discount, code }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <OrderSummaryRowWrapper>
            <OrderSummaryContent>
                <OrderSummaryRow>
                    <OrderSummaryTextAndImage>{`${t('Promo code')}: ${code}`}</OrderSummaryTextAndImage>
                    {isPriceVisible(discount.priceWithVat) && Number(discount.priceWithVat) > 0 && (
                        <OrderSummaryPrice>
                            <p className="text-sm font-bold">-{formatPrice(discount.priceWithVat)}</p>
                        </OrderSummaryPrice>
                    )}
                </OrderSummaryRow>
            </OrderSummaryContent>
        </OrderSummaryRowWrapper>
    );
};
