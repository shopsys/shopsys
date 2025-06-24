import { useCartPageNavigation } from './cartUtils';
import { Flag } from 'components/Basic/Flag/Flag';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemovePromoCodeFromCart } from 'utils/cart/useRemovePromoCodeFromCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

export const CartPreview: FC = () => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, promoCodes } = useCurrentCart();
    const { goToNextStepFromCartPage } = useCartPageNavigation();

    const { removePromoCodeFromCart, isRemovingPromoCodeFromCart } = useRemovePromoCodeFromCart({
        success: t('Promo code was removed from the order.'),
        error: t('There was an error while removing the promo code from the order.'),
    });

    if (!cart?.items.length) {
        return null;
    }

    return (
        <div className="bg-background-more font-secondary vl:max-w-[495px] w-full rounded-xl px-4 py-6 text-center font-semibold sm:p-8">
            {isRemovingPromoCodeFromCart && <LoaderWithOverlay className="w-5" overlayClassName="rounded-xl" />}

            {promoCodes.length > 0 && (
                <div className="border-border-less mb-4 flex flex-col gap-4 border-b-[3px] pb-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <p>{t('Promo code')}</p>

                            <Flag type="discount">{promoCodes[0].code}</Flag>

                            <button
                                aria-label={t('Remove promo code {{ promoCode }}', { promoCode: promoCodes[0].code })}
                                className="text-link-default hover:text-link-hovered cursor-pointer text-xs underline hover:no-underline"
                                data-tid={TIDs.blocks_promocode_promocodeinfo_code}
                                tabIndex={0}
                                onClick={() => removePromoCodeFromCart(promoCodes[0].code)}
                            >
                                {t('Remove')}
                            </button>
                        </div>
                    </div>

                    {isPriceVisible(cart.totalDiscountPrice.priceWithVat) &&
                        mapPriceForCalculations(cart.totalDiscountPrice.priceWithVat) > 0 && (
                            <div className="flex items-center justify-between">
                                <p>{t('The amount of discounts')}</p>

                                <span className="text-price-discounted whitespace-nowrap">
                                    {'-' + formatPrice(cart.totalDiscountPrice.priceWithVat)}
                                </span>
                            </div>
                        )}
                </div>
            )}

            {isPriceVisible(cart.totalItemsPrice.priceWithVat) &&
                isPriceVisible(cart.totalItemsPrice.priceWithoutVat) && (
                    <div className="flex flex-col justify-between gap-2" data-tid={TIDs.pages_cart_cartpreview_total}>
                        <div className="flex items-center justify-between">
                            <p>{t('Total')}</p>

                            <span className="text-price-default text-lg whitespace-nowrap sm:text-2xl">
                                {formatPrice(cart.totalItemsPrice.priceWithVat)}
                            </span>
                        </div>

                        <span className="text-price-before text-right text-sm whitespace-nowrap">
                            {formatPrice(cart.totalItemsPrice.priceWithoutVat)} {t('without VAT')}
                        </span>
                    </div>
                )}

            <Button
                aria-label={t('Continue with order to {{ step }}', { step: t('Transport and payment') })}
                className="mt-4"
                size="xlarge"
                tid={TIDs.blocks_orderaction_next}
                variant="primary"
                onClick={goToNextStepFromCartPage}
            >
                {t('Continue with order')}
                <ArrowSecondaryIcon className="size-4 -rotate-90" />
            </Button>
        </div>
    );
};
