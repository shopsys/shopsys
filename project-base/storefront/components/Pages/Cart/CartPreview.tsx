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
    const { cart, promoCode } = useCurrentCart();
    const { goToNextStepFromCartPage } = useCartPageNavigation();

    const { removePromoCodeFromCart, isRemovingPromoCodeFromCart } = useRemovePromoCodeFromCart({
        success: t('Promo code was removed from the order.'),
        error: t('There was an error while removing the promo code from the order.'),
    });

    const buttonContinue = (
        <Button
            className="mt-4"
            size="xlarge"
            tid={TIDs.blocks_orderaction_next}
            variant="primary"
            onClick={goToNextStepFromCartPage}
        >
            {t('Continue with order')}
            <ArrowSecondaryIcon className="size-4 -rotate-90" />
        </Button>
    );

    if (!cart?.items.length) {
        return null;
    }

    if (!isPriceVisible(cart.totalItemsPrice.priceWithVat)) {
        return buttonContinue;
    }

    return (
        <div className="w-full rounded-xl bg-backgroundMore px-4 py-6 text-center font-secondary font-semibold sm:p-8 vl:max-w-[495px]">
            {mapPriceForCalculations(cart.totalDiscountPrice.priceWithVat) > 0 && (
                <div className="mb-4 flex flex-col gap-4 border-b-[3px] border-borderAccentLess pb-4">
                    {isRemovingPromoCodeFromCart && <LoaderWithOverlay className="w-5" />}

                    {promoCode !== null && (
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <p>{t('Promo code')}</p>

                                <Flag type="discount">{promoCode.code}</Flag>

                                <button
                                    className="text-xs text-link underline hover:text-linkHovered hover:no-underline"
                                    tid={TIDs.blocks_promocode_promocodeinfo_code}
                                    onClick={() => removePromoCodeFromCart(promoCode.code)}
                                >
                                    {t('Remove')}
                                </button>
                            </div>
                        </div>
                    )}

                    <div className="flex items-center justify-between" tid={TIDs.pages_cart_cartpreview_discount}>
                        <p>{t('The amount of discounts')}</p>

                        <span className="whitespace-nowrap text-priceDiscounted">
                            {'-' + formatPrice(cart.totalDiscountPrice.priceWithVat)}
                        </span>
                    </div>
                </div>
            )}

            <div className="flex flex-col justify-between gap-2" tid={TIDs.pages_cart_cartpreview_total}>
                <div className="flex items-center justify-between">
                    <p>{t('Total')}</p>

                    <span className="whitespace-nowrap text-lg text-price sm:text-2xl">
                        {formatPrice(cart.totalItemsPrice.priceWithVat)}
                    </span>
                </div>

                <span className="whitespace-nowrap text-right text-sm text-priceBefore">
                    {formatPrice(cart.totalItemsPrice.priceWithoutVat)} {t('without VAT')}
                </span>
            </div>

            {buttonContinue}
        </div>
    );
};
