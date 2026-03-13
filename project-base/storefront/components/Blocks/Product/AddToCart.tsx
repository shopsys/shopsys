import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { useAddToCartAriaLabel } from 'utils/accessibility/useAddToCartAriaLabel';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type AddToCartProps = {
    productUuid: string;
    minQuantity: number;
    maxQuantity: number | null;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    isWithSpinbox?: boolean;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonVariant?: 'primary' | 'inverted';
    showResponsiveCartIcon?: boolean;
    tabIndex?: number;
    ariaProductName: string;
    ariaPrice: string;
    ariaUnit: string;
};

export const AddToCart: FC<AddToCartProps> = ({
    productUuid,
    minQuantity,
    maxQuantity,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    className,
    isWithSpinbox = true,
    buttonSize = 'medium',
    buttonVariant = 'primary',
    showResponsiveCartIcon = false,
    tabIndex = 0,
    ariaProductName,
    ariaPrice,
    ariaUnit,
}) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();

    const { onAddToCartHandler, isAddingToCart } = useAddToCartHandler({
        spinboxRef,
        productUuid,
        gtmMessageOrigin,
        gtmProductListName,
        isWithSpinbox,
        listIndex,
    });

    const { ariaLabel, onFocusHandler } = useAddToCartAriaLabel({
        spinboxRef,
        productName: ariaProductName,
        priceWithVat: ariaPrice,
        unitName: ariaUnit,
    });

    return (
        <div className={twMergeCustom('flex items-center justify-between gap-2', className)}>
            {isWithSpinbox && (
                <Spinbox
                    defaultValue={1}
                    id={productUuid}
                    max={maxQuantity}
                    min={minQuantity}
                    ref={spinboxRef}
                    size={buttonSize}
                    step={1}
                />
            )}

            <div className="relative">
                {isAddingToCart && (
                    <Loader className="z-overlay bg-background-more absolute inset-0 flex h-full w-full items-center justify-center rounded-sm py-2 opacity-50" />
                )}

                <Button
                    aria-haspopup="dialog"
                    aria-label={ariaLabel}
                    disabled={isAddingToCart}
                    hasDisabledLook={isAddingToCart}
                    name="add-to-cart"
                    size={buttonSize}
                    tabIndex={tabIndex}
                    tid={TIDs.blocks_product_addtocart}
                    variant={buttonVariant}
                    onClick={onAddToCartHandler}
                    onFocus={onFocusHandler}
                >
                    {showResponsiveCartIcon && <CartIcon className="size-5 md:hidden" />}
                    <span className={showResponsiveCartIcon ? 'hidden md:block' : ''}>{t('Add to cart')}</span>
                </Button>
            </div>
        </div>
    );
};
