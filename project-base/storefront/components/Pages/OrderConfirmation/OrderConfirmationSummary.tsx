import { Flag } from 'components/Basic/Flag/Flag';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import React, { memo } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type OrderConfirmationSummaryProps = {
    promoCode: string | null | undefined;
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
};

const OrderConfirmationSummaryComp: FC<OrderConfirmationSummaryProps> = ({
    promoCode,
    payment,
    transport,
    totalPrice,
    roundingPrice,
}) => {
    const formatPrice = useFormatPrice();
    const { t } = useTranslation();

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

export const OrderConfirmationSummary = memo(OrderConfirmationSummaryComp);
