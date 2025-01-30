import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import React from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type OrderConfirmationSummaryProps = {
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

export const OrderConfirmationSummary: FC<OrderConfirmationSummaryProps> = ({ payment, transport, totalPrice }) => {
    const formatPrice = useFormatPrice();
    const { t } = useTranslation();

    return (
        <div className="flex flex-col gap-4 rounded-xl bg-backgroundMore p-8 font-secondary text-sm font-semibold">
            <div className="flex items-center justify-between gap-4">
                <span>
                    {t('Transport')}&nbsp;- {transport.name}
                </span>
                {isPriceVisible(transport.price) && <span>{formatPrice(transport.price)}</span>}
            </div>
            <div className="flex items-center justify-between gap-4">
                <span>
                    {t('Payment')}&nbsp;- {payment.name}
                </span>
                {isPriceVisible(transport.price) && <span>{formatPrice(payment.price)}</span>}
            </div>

            {isPriceVisible(totalPrice.priceWithVat) && (
                <div className="flex items-center justify-between gap-4 border-t-[3px] border-borderAccentLess pt-4">
                    <span>{t('Total')}</span>
                    <div className="flex flex-col gap-2">
                        <span className="text-lg font-bold text-price">{formatPrice(totalPrice.priceWithVat)}</span>
                        <span className="whitespace-nowrap text-sm font-semibold tracking-wide text-textSubtle">
                            {formatPrice(totalPrice.priceWithoutVat)} {t('without VAT')}
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
};
