import { Flag } from 'components/Basic/Flag/Flag';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderConfirmationSummaryProps = {
    promoCode?: string | null;
    payment: {
        name: string | undefined;
        price: string | undefined;
    };
    transport: {
        name: string | undefined;
        price: string | undefined;
    };
    totalPrice: TypePriceFragment;
    roundingPrice?: TypePriceFragment | null;
    totalItemsPriceBeforeDiscount?: TypePriceFragment | null;
    totalDiscountPrice?: TypePriceFragment | null;
};

export const OrderConfirmationSummary: FC<OrderConfirmationSummaryProps> = ({
    promoCode,
    payment,
    transport,
    totalPrice,
    roundingPrice,
    totalItemsPriceBeforeDiscount,
    totalDiscountPrice,
}) => {
    const formatPrice = useFormatPrice();
    const { t } = useTranslation();

    const hasTotalDiscounts =
        totalDiscountPrice &&
        isPriceVisible(totalDiscountPrice.priceWithVat) &&
        mapPriceForCalculations(totalDiscountPrice.priceWithVat) > 0;

    return (
        <div className="bg-background-more font-secondary flex flex-col gap-4 rounded-xl p-8 text-sm font-semibold">
            <div className="flex items-center justify-between gap-4">
                {t('Transport')}
                {transport.name ? (
                    <>&nbsp;- {transport.name}</>
                ) : (
                    <span className="text-text-less">{t('Choose transport')}</span>
                )}
                {transport.price && isPriceVisible(transport.price) && <span>{formatPrice(transport.price)}</span>}
            </div>

            {transport.name && (
                <div className="flex items-center justify-between gap-4">
                    {t('Payment')}
                    {payment.name ? (
                        <>&nbsp;- {payment.name}</>
                    ) : (
                        <span className="text-text-less">{t('Choose payment')}</span>
                    )}
                    {payment.price && isPriceVisible(payment.price) && <span>{formatPrice(payment.price)}</span>}
                </div>
            )}

            {roundingPrice && isPriceVisible(roundingPrice.priceWithVat) && (
                <div className="flex items-center justify-between gap-4">
                    {t('Rounding')}
                    <span>{formatPrice(roundingPrice.priceWithVat)}</span>
                </div>
            )}

            {promoCode && (
                <div className={twJoin('flex items-center justify-between gap-4')}>
                    {t('Promo code')}
                    <Flag type="discount">{promoCode}</Flag>
                </div>
            )}

            {hasTotalDiscounts && (
                <div className="flex flex-col gap-4">
                    {totalItemsPriceBeforeDiscount && isPriceVisible(totalItemsPriceBeforeDiscount.priceWithVat) && (
                        <div className="border-border-less flex items-center justify-between gap-4 border-t-1 pt-4">
                            <span>{t('Price before discount')}</span>

                            <span className="whitespace-nowrap">
                                {formatPrice(totalItemsPriceBeforeDiscount.priceWithVat)}
                            </span>
                        </div>
                    )}

                    <div className="border-border-less flex items-center justify-between gap-4">
                        <span className="text-price-discounted">{t('Save in total')}</span>

                        <span className="text-price-discounted whitespace-nowrap">
                            {formatPrice(totalDiscountPrice.priceWithVat)}
                        </span>
                    </div>
                </div>
            )}

            {isPriceVisible(totalPrice.priceWithVat) && isPriceVisible(totalPrice.priceWithoutVat) && (
                <div className="border-border-less flex items-center justify-between gap-4 border-t-[3px] pt-4">
                    <span>{t('Total')}</span>
                    <div className="flex flex-col gap-2">
                        <span className="text-price-default text-lg font-bold">
                            {formatPrice(totalPrice.priceWithVat)}
                        </span>
                        <span className="text-text-less text-sm font-semibold tracking-wide whitespace-nowrap">
                            {formatPrice(totalPrice.priceWithoutVat)} {t('without VAT')}
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
};
