import { useCartPageNavigation } from './cartUtils';
import { Flag } from 'components/Basic/Flag/Flag';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { RefObject } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemovePromoCodeFromCart } from 'utils/cart/useRemovePromoCodeFromCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type CartPreviewProps = {
    wrapperRef?: RefObject<HTMLDivElement>;
};

export const CartPreview: FC<CartPreviewProps> = ({ wrapperRef }) => {
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

    const hasProductDiscounts =
        isPriceVisible(cart.totalProductPriceAdjustmentsDiscount.priceWithVat) &&
        mapPriceForCalculations(cart.totalProductPriceAdjustmentsDiscount.priceWithVat) > 0;

    const hasTotalDiscounts =
        isPriceVisible(cart.totalDiscountPrice.priceWithVat) &&
        mapPriceForCalculations(cart.totalDiscountPrice.priceWithVat) > 0;

    const hasPromoCodeDiscount =
        promoCodes.length > 0 &&
        isPriceVisible(promoCodes[0].discountPrice.priceWithVat) &&
        mapPriceForCalculations(promoCodes[0].discountPrice.priceWithVat) > 0;

    return (
        <div className="bg-background-more font-secondary vl:max-w-[495px] w-full rounded-xl px-4 py-6 text-center font-semibold sm:p-8">
            {isRemovingPromoCodeFromCart && <LoaderWithOverlay className="w-5" overlayClassName="rounded-xl" />}

            {(hasProductDiscounts || promoCodes.length > 0) && (
                <div className="border-border-less mb-4 flex flex-col gap-4 border-b-[3px] pb-4">
                    {isPriceVisible(cart.totalItemsPriceBeforeDiscount.priceWithVat) && hasTotalDiscounts && (
                        <div className="flex items-center justify-between">
                            <p>{t('Price before discount')}</p>

                            <span className="whitespace-nowrap">
                                {formatPrice(cart.totalItemsPriceBeforeDiscount.priceWithVat)}
                            </span>
                        </div>
                    )}

                    {hasProductDiscounts && (
                        <div className="flex items-center justify-between">
                            <p>{t('Amount of discount on products on sale')}</p>

                            <span className="text-price-discounted whitespace-nowrap">
                                {'-' + formatPrice(cart.totalProductPriceAdjustmentsDiscount.priceWithVat)}
                            </span>
                        </div>
                    )}

                    {promoCodes.length > 0 && (
                        <div className="flex w-full items-center gap-2">
                            <p>{t('Promo code')}</p>

                            <Flag className={hasPromoCodeDiscount ? '' : 'ml-auto'} type="discount">
                                {promoCodes[0].code}
                            </Flag>

                            <button
                                className="text-link-default hover:text-link-hovered cursor-pointer text-xs underline hover:no-underline"
                                data-tid={TIDs.blocks_promocode_promocodeinfo_code}
                                tabIndex={0}
                                aria-label={t('Remove promo code {{ promoCode }}', {
                                    ns: 'accessibility',
                                    promoCode: promoCodes[0].code,
                                })}
                                onClick={() => removePromoCodeFromCart(promoCodes[0].code)}
                            >
                                {t('Remove')}
                            </button>

                            {hasPromoCodeDiscount && (
                                <span className="text-price-discounted ml-auto">
                                    {'-' + formatPrice(promoCodes[0].discountPrice.priceWithVat)}
                                </span>
                            )}
                        </div>
                    )}

                    {hasTotalDiscounts && (
                        <div className="border-border-less flex items-center justify-between border-t-1 pt-4">
                            <p className="text-price-discounted">{t('Save in total')}</p>

                            <span className="text-price-discounted whitespace-nowrap">
                                {formatPrice(cart.totalDiscountPrice.priceWithVat)}
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

            <div className="mt-4" ref={wrapperRef}>
                <Button
                    size="xlarge"
                    tid={TIDs.blocks_orderaction_next}
                    variant="primary"
                    aria-label={t('Continue with order to {{ step }}', {
                        ns: 'accessibility',
                        step: t('Transport and payment'),
                    })}
                    onClick={goToNextStepFromCartPage}
                >
                    {t('Continue with order')}
                    <ArrowSecondaryIcon className="size-4 -rotate-90" />
                </Button>
            </div>
        </div>
    );
};
