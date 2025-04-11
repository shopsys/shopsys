import { Flag } from 'components/Basic/Flag/Flag';
import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import React from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type OrderConfirmationSummaryProps = {
    promoCode: string | null;
    payment: {
        name: string;
        price: string;
    };
    transport: {
        name: string;
        price: string;
    };
    totalPrice: TypePriceFragment;
};

export const OrderConfirmationSummary: FC<OrderConfirmationSummaryProps> = ({
    promoCode,
    payment,
    transport,
    totalPrice,
}) => {
    const formatPrice = useFormatPrice();
    const { t } = useTranslation();

    return (
        <div className="bg-backgroundMore font-secondary flex flex-col gap-4 rounded-xl p-8 text-sm font-semibold">
            <div className="flex items-center justify-between gap-4">
                {t('Transport')}&nbsp;- {transport.name}
                {isPriceVisible(transport.price) && <span>{formatPrice(transport.price)}</span>}
            </div>

            <div className="flex items-center justify-between gap-4">
                {t('Payment')}&nbsp;- {payment.name}
                {isPriceVisible(transport.price) && <span>{formatPrice(payment.price)}</span>}
            </div>

            {promoCode && (
                <div className={twJoin('flex items-center justify-between gap-4')}>
                    {t('Promo code')}
                    <Flag type="discount">{promoCode}</Flag>
                </div>
            )}

            {isPriceVisible(totalPrice.priceWithVat) && isPriceVisible(totalPrice.priceWithoutVat) && (
                <div className="border-borderAccentLess flex items-center justify-between gap-4 border-t-[3px] pt-4">
                    <span>{t('Total')}</span>
                    <div className="flex flex-col gap-2">
                        <span className="text-price text-lg font-bold">{formatPrice(totalPrice.priceWithVat)}</span>
                        <span className="text-textSubtle text-sm font-semibold tracking-wide whitespace-nowrap">
                            {formatPrice(totalPrice.priceWithoutVat)} {t('without VAT')}
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
};
