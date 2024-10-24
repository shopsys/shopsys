import {
    OrderSummaryContent,
    OrderSummaryPrice,
    OrderSummaryRow,
    OrderSummaryRowWrapper,
    OrderSummaryTextAndImage,
} from './OrderSummaryElements';
import { TypePromoCode, TypePromoCodeTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { formatPercent } from 'utils/formaters/formatPercent';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type PromoCodeProps = {
    promoCode: TypePromoCode;
};

const PromoCodeValue: FC<{ value: string; type: TypePromoCodeTypeEnum }> = ({ value, type }) => {
    const formatPrice = useFormatPrice();

    const isNominal = type === TypePromoCodeTypeEnum.Nominal;
    const isPercent = type === TypePromoCodeTypeEnum.Percent;

    return (
        <p className="text-sm font-bold">-{isNominal ? formatPrice(value) : isPercent ? formatPercent(value) : null}</p>
    );
};

export const PromoCode: FC<PromoCodeProps> = ({ promoCode }) => {
    const { t } = useTranslation();
    const { discount, code, type } = promoCode;

    return (
        <OrderSummaryRowWrapper>
            <OrderSummaryContent>
                <OrderSummaryRow>
                    <OrderSummaryTextAndImage>{`${t('Promo code')}: ${code}`}</OrderSummaryTextAndImage>
                    {isPriceVisible(discount.priceWithVat) && (
                        <OrderSummaryPrice>
                            <PromoCodeValue type={type} value={discount.priceWithVat} />
                        </OrderSummaryPrice>
                    )}
                </OrderSummaryRow>
            </OrderSummaryContent>
        </OrderSummaryRowWrapper>
    );
};
