import { Flag } from 'components/Basic/Flag/Flag';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { twJoin } from 'tailwind-merge';
import { AppliedGiftVoucherType } from 'types/cart';
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
    giftVouchers?: AppliedGiftVoucherType[];
    remainingAmountToPay?: string;
};

export const OrderConfirmationSummary: FC<OrderConfirmationSummaryProps> = ({
    promoCode,
    payment,
    transport,
    totalPrice,
    roundingPrice,
    totalItemsPriceBeforeDiscount,
    totalDiscountPrice,
    giftVouchers,
    remainingAmountToPay,
}) => {
    const formatPrice = useFormatPrice();
    const { t } = useTranslation();

    const hasTotalDiscounts =
        totalDiscountPrice &&
        isPriceVisible(totalDiscountPrice.priceWithVat) &&
        mapPriceForCalculations(totalDiscountPrice.priceWithVat) > 0;

    return (
        <div className="flex flex-col gap-4 rounded-xl bg-background-more p-8 font-secondary font-semibold text-sm">
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
                    {t('Discount coupon')}
                    <Flag type="discount">{promoCode}</Flag>
                </div>
            )}

            {hasTotalDiscounts && (
                <div className="flex flex-col gap-4">
                    {totalItemsPriceBeforeDiscount && isPriceVisible(totalItemsPriceBeforeDiscount.priceWithVat) && (
                        <div className="flex items-center justify-between gap-4 border-border-less border-t pt-4">
                            <span>{t('Price before discount')}</span>

                            <span className="whitespace-nowrap">
                                {formatPrice(totalItemsPriceBeforeDiscount.priceWithVat)}
                            </span>
                        </div>
                    )}

                    <div className="flex items-center justify-between gap-4 border-border-less">
                        <span className="text-price-discounted">{t('Save in total')}</span>

                        <span className="whitespace-nowrap text-price-discounted">
                            {formatPrice(totalDiscountPrice.priceWithVat)}
                        </span>
                    </div>
                </div>
            )}

            {isPriceVisible(totalPrice.priceWithVat) && isPriceVisible(totalPrice.priceWithoutVat) && (
                <div className="flex items-center justify-between gap-4 border-border-less border-t-3 pt-4">
                    <span>{t('Total')}</span>
                    <div className="flex flex-col gap-2">
                        <span className="font-bold text-lg text-price-default">
                            {formatPrice(totalPrice.priceWithVat)}
                        </span>
                        <span className="whitespace-nowrap font-semibold text-sm text-text-less tracking-wide">
                            {formatPrice(totalPrice.priceWithoutVat)} {t('without VAT')}
                        </span>
                    </div>
                </div>
            )}

            {!!giftVouchers?.length && (
                <div className="flex flex-col gap-4">
                    {giftVouchers.map((giftVoucher) => (
                        <div key={giftVoucher.code} className="flex items-center justify-between gap-4">
                            <span className="flex items-center gap-2">
                                {t('Gift voucher')}

                                <Flag type="discount">{giftVoucher.code}</Flag>
                            </span>

                            {isPriceVisible(giftVoucher.valueWithVat) && (
                                <span className="whitespace-nowrap text-price-discounted">
                                    {`-${formatPrice(giftVoucher.valueWithVat)}`}
                                </span>
                            )}
                        </div>
                    ))}

                    {remainingAmountToPay !== undefined && isPriceVisible(remainingAmountToPay) && (
                        <div className="flex items-baseline justify-between gap-4 border-border-less border-t-[3px] pt-4">
                            <span className="text-lg">{t('Remaining to pay')}</span>

                            <span className="whitespace-nowrap font-bold text-lg text-price-default">
                                {formatPrice(remainingAmountToPay, { explicitZero: true })}
                            </span>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};
