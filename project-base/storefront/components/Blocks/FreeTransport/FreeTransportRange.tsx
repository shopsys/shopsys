import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

const barWrapperTwClass = 'relative order-1 h-[4px] vl:order-0';
const barTwClass = 'absolute top-1/2 left-0 h-[4px] w-full bg-borderAccentLess';
const barRangeTwClass =
    'relative h-[4px] rounded-md bg-actionPrimaryBorder transition-width ease-defaultTransition duration-200';
const barRangeFullTwClass = barRangeTwClass + ' w-full';
const freeTransportRangeTwClass = twJoin(
    'flex w-full flex-col text-[11px] leading-[14px] mb-7 max-w-[180px] text-center',
    'lg:text-xs lg:max-w-[260px] lg:leading-5 vl:mb-0 vl:block',
);

const FreeTransportRange: FC = () => {
    const testIdentifier = 'blocks-freetransport-range';
    const { cart } = useCurrentCart();
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    const shouldDisplayTransportBar = cart?.remainingAmountWithVatForFreeTransport !== null;
    const totalItemPrice = Number(cart?.totalItemsPrice.priceWithVat);
    const totalPriceRemaining = Number(cart?.remainingAmountWithVatForFreeTransport);
    const totalPriceRemainingPercents =
        totalItemPrice && totalPriceRemaining ? (totalItemPrice / (totalItemPrice + totalPriceRemaining)) * 100 : 0;

    if (cart?.items.length === 0 || !shouldDisplayTransportBar) {
        return null;
    }

    const amountFormatted = formatPrice(totalPriceRemaining);

    if (totalPriceRemaining > 0) {
        return (
            <div className={freeTransportRangeTwClass} data-testid={testIdentifier}>
                <Trans
                    defaultTrans="Buy for {{ amount }} and <0 /> get free shipping!"
                    i18nKey="freeShippingBarText"
                    values={{ amount: amountFormatted }}
                    components={{
                        0: <br className="lg:hidden" />,
                    }}
                />
                <div className={barWrapperTwClass}>
                    <div className={barTwClass}>
                        <div
                            className={barRangeTwClass}
                            style={{ width: Math.min(totalPriceRemainingPercents, 100) + '%' }}
                        />
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className={freeTransportRangeTwClass} data-testid={testIdentifier}>
            <strong>{t('Your delivery and payment is now free of charge!')}</strong>
            <div className={barWrapperTwClass}>
                <div className={barTwClass}>
                    <div className={barRangeFullTwClass} />
                </div>
            </div>
        </div>
    );
};

export default FreeTransportRange;
