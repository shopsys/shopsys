import useTranslation from 'next-translate/useTranslation';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

export const FreeTransportRange: FC = () => {
    const testIdentifier = 'blocks-freetransport-range';
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
            <span className="font-secondary text-sm font-semibold">
                {totalPriceRemaining > 0
                    ? t('Buy for {{ amount }} and get free shipping!', { amount: amountFormatted })
                    : t('Your delivery and payment is now free of charge!')}
            </span>

            <div className="vl:order-0 relative order-1 h-[4px]">
                <div className="bg-border-less absolute top-1/2 left-0 h-[4px] w-full">
                    <div
                        className="transition-width ease-defaultTransition bg-background-success relative h-[4px] rounded-md duration-200"
                        style={{
                            width: totalPriceRemaining > 0 ? Math.min(totalPriceRemainingPercents, 100) + '%' : '100%',
                        }}
                    />
                </div>
            </div>
        </div>
    );
};
