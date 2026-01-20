import { useCartPageNavigation } from './cartUtils';
import { Flag } from 'components/Basic/Flag/Flag';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { RefObject } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemovePromoCodeFromCart } from 'utils/cart/useRemovePromoCodeFromCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type CartPreviewProps = {
    wrapperRef?: RefObject<HTMLDivElement>;
    isFirstStep?: boolean;
    isTransportOrPaymentLoading?: boolean;
};

export const CartPreview: FC<CartPreviewProps> = ({
    wrapperRef,
    isFirstStep = false,
    isTransportOrPaymentLoading = false,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, promoCodes, transport, payment, roundingPrice } = useCurrentCart();
    const { goToNextStepFromCartPage } = useCartPageNavigation();

    const { removePromoCodeFromCart, isRemovingPromoCodeFromCart } = useRemovePromoCodeFromCart({
        success: t('Promo code was removed from the order.'),
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

    const totalPrice = isFirstStep
        ? isPriceVisible(cart.totalItemsPrice.priceWithVat) && formatPrice(cart.totalItemsPrice.priceWithVat)
        : isPriceVisible(cart.totalPrice.priceWithVat) && formatPrice(cart.totalPrice.priceWithVat);

    const totalPriceWithoutVat = isFirstStep
        ? isPriceVisible(cart.totalItemsPrice.priceWithoutVat) && formatPrice(cart.totalItemsPrice.priceWithoutVat)
        : isPriceVisible(cart.totalPrice.priceWithoutVat) && formatPrice(cart.totalPrice.priceWithoutVat);

    return (
        <div
            className={twJoin(
                'bg-background-more font-secondary vl:max-w-[495px] relative flex w-full flex-col gap-4 rounded-xl px-4 py-6 font-semibold sm:p-8',
                !isFirstStep && 'text-sm',
            )}
        >
            {(isRemovingPromoCodeFromCart || isTransportOrPaymentLoading) && (
                <LoaderWithOverlay className="size-5" overlayClassName="rounded-xl" />
            )}

            {!isFirstStep && (
                <>
                    <div className="flex items-center justify-between gap-4">
                        {t('Transport')}

                        {transport?.name ? (
                            <>&nbsp;- {transport.name}</>
                        ) : (
                            <span className="text-text-less">{t('Choose transport')}</span>
                        )}
                        {transport?.price && isPriceVisible(transport.price.priceWithVat) && (
                            <span>{formatPrice(transport.price.priceWithVat)}</span>
                        )}
                    </div>

                    {transport?.name && (
                        <div className="flex items-center justify-between gap-4">
                            {t('Payment')}

                            {payment?.name ? (
                                <>&nbsp;- {payment.name}</>
                            ) : (
                                <span className="text-text-less">{t('Choose payment')}</span>
                            )}
                            {payment?.price && isPriceVisible(payment.price.priceWithVat) && (
                                <span>{formatPrice(payment.price.priceWithVat)}</span>
                            )}
                        </div>
                    )}

                    {roundingPrice && isPriceVisible(roundingPrice.priceWithVat) && (
                        <div className="flex items-center justify-between gap-4">
                            {t('Rounding')}
                            <span>{formatPrice(roundingPrice.priceWithVat)}</span>
                        </div>
                    )}
                </>
            )}

            {(hasProductDiscounts || promoCodes.length > 0) && (
                <div className={twJoin('flex flex-col gap-4', !isFirstStep && 'border-border-less border-t-1 pt-4')}>
                    {isPriceVisible(cart.totalItemsPriceBeforeDiscount.priceWithVat) && hasTotalDiscounts && (
                        <div className="flex items-center justify-between">
                            <span>{t('Price before discount')}</span>

                            <span className="whitespace-nowrap">
                                {formatPrice(cart.totalItemsPriceBeforeDiscount.priceWithVat)}
                            </span>
                        </div>
                    )}

                    {hasProductDiscounts && (
                        <div className="flex items-center justify-between">
                            <span>{t('Amount of discount on products on sale')}</span>

                            <span className="text-price-discounted whitespace-nowrap">
                                {'-' + formatPrice(cart.totalProductPriceAdjustmentsDiscount.priceWithVat)}
                            </span>
                        </div>
                    )}

                    {promoCodes.length > 0 && (
                        <div className="flex w-full items-center gap-2">
                            <span>{t('Promo code')}</span>

                            <Flag className={hasPromoCodeDiscount ? '' : 'ml-auto'} type="discount">
                                {promoCodes[0].code}
                            </Flag>

                            {isFirstStep && (
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
                            )}

                            {hasPromoCodeDiscount && (
                                <span className="text-price-discounted ml-auto">
                                    {'-' + formatPrice(promoCodes[0].discountPrice.priceWithVat)}
                                </span>
                            )}
                        </div>
                    )}

                    {hasTotalDiscounts && (
                        <div className="border-border-less flex items-center justify-between border-t-1 pt-4">
                            <span className="text-price-discounted">{t('Save in total')}</span>

                            <span className="text-price-discounted whitespace-nowrap">
                                {formatPrice(cart.totalDiscountPrice.priceWithVat)}
                            </span>
                        </div>
                    )}
                </div>
            )}

            {(totalPrice || totalPriceWithoutVat) && (
                <div
                    data-tid={TIDs.pages_cart_cart_preview_total}
                    className={twJoin(
                        'flex justify-between gap-2 align-baseline',
                        (!isFirstStep || hasProductDiscounts || promoCodes.length > 0) &&
                            'border-border-less border-t-[3px] pt-4',
                    )}
                >
                    <span>{t('Total')}</span>

                    <div className="flex flex-col gap-2">
                        <span
                            className={twJoin(
                                'text-price-default whitespace-nowrap',
                                isFirstStep ? 'text-lg sm:text-2xl' : 'text-lg',
                            )}
                        >
                            {totalPrice}
                        </span>

                        <span className="text-price-before text-right text-sm whitespace-nowrap">
                            {totalPriceWithoutVat} {t('without VAT')}
                        </span>
                    </div>
                </div>
            )}

            {isFirstStep && (
                <div className="text-center" ref={wrapperRef}>
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
            )}
        </div>
    );
};
