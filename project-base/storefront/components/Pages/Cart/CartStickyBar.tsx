import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { FreeTransportRange } from 'components/Blocks/FreeTransport/FreeTransportRange';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { RefObject } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { useIntersectionObserver } from 'utils/ui/useIntersectionObserver';
import { useCartPageNavigation } from './cartUtils';

const MIN_ITEMS_FOR_STICKY_BAR = 4;

type CartStickyBarProps = {
    originalButtonRef: RefObject<HTMLDivElement | null>;
};

export const CartStickyBar: FC<CartStickyBarProps> = ({ originalButtonRef }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart } = useCurrentCart();
    const { goToNextStepFromCartPage } = useCartPageNavigation();

    const shouldShowStickyBar = cart && cart.items.length > MIN_ITEMS_FOR_STICKY_BAR;

    const { isIntersecting: isEndOfList } = useIntersectionObserver({
        ref: originalButtonRef,
        enabled: !!shouldShowStickyBar,
    });

    if (!shouldShowStickyBar) {
        return null;
    }

    return (
        <div
            className={`fixed right-0 bottom-0 left-0 z-above bg-background-default shadow-[0_-4px_16px_rgba(0,0,0,0.1)] transition-transform duration-300 ${
                isEndOfList ? 'translate-y-full' : 'translate-y-0'
            }`}
        >
            <Webline>
                <div className="flex items-center justify-between gap-4 py-4">
                    <div className="hidden w-full lg:block lg:max-w-[424px]">
                        <FreeTransportRange />
                    </div>

                    <div className="ml-auto flex w-full items-center justify-between gap-4 lg:justify-end">
                        {isPriceVisible(cart.totalItemsPrice.priceWithVat) && (
                            <div className="flex flex-col font-secondary font-semibold">
                                <p className="text-sm">{t('Total price')}</p>

                                <span className="whitespace-nowrap text-price-default">
                                    {formatPrice(cart.totalItemsPrice.priceWithVat)}
                                </span>
                            </div>
                        )}

                        <Button
                            size="large"
                            aria-label={t('Continue with order to {{ step }}', {
                                ns: 'accessibility',
                                step: t('Transport and payment'),
                            })}
                            onClick={goToNextStepFromCartPage}
                        >
                            <span className="hidden sm:inline">{t('Continue with order')}</span>
                            <span className="sm:hidden">{t('Continue')}</span>

                            <ArrowSecondaryIcon className="size-4 -rotate-90" />
                        </Button>
                    </div>
                </div>
            </Webline>
        </div>
    );
};
