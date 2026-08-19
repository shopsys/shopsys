import { TIDs } from 'cypress/tids';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const FreeTransportRange: FC = () => {
    const testIdentifier = TIDs.free_transport_range;
    const { cart } = useCurrentCart();
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    const shouldDisplayTransportBar = cart?.remainingAmountForFreeTransport !== null;
    const totalItemPrice = Number(cart?.totalItemsPrice.priceWithVat);
    const totalPriceRemaining = Number(cart?.remainingAmountForFreeTransport);
    const totalPriceRemainingPercents =
        totalItemPrice && totalPriceRemaining ? (totalItemPrice / (totalItemPrice + totalPriceRemaining)) * 100 : 0;

    if (cart?.items.length === 0 || !shouldDisplayTransportBar) {
        return null;
    }

    const amountFormatted = formatPrice(totalPriceRemaining);

    return (
        <div className="flex w-full flex-col gap-2.5 lg:gap-1" data-tid={testIdentifier}>
            <span className="font-secondary font-semibold text-sm">
                {totalPriceRemaining > 0
                    ? t('Buy for {{ amount }} and get free shipping!', { amount: amountFormatted })
                    : t('Your delivery and payment is now free of charge!')}
            </span>

            <div className="relative order-1 vl:order-0 h-1">
                <div className="absolute top-1/2 left-0 h-1 w-full bg-border-less">
                    <div
                        className="relative h-1 rounded-md bg-background-success transition-width duration-200 ease-defaultTransition"
                        style={{
                            width: totalPriceRemaining > 0 ? `${Math.min(totalPriceRemainingPercents, 100)}%` : '100%',
                        }}
                    />
                </div>
            </div>
        </div>
    );
};
