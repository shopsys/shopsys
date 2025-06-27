import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Popup/AddToCartPopup').then((component) => component.AddToCartPopup),
);

type AddToCartProps = {
    productUuid: string;
    minQuantity: number;
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
    const { addToCart, isAddingToCart } = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const formatPrice = useFormatPrice();

    const onAddToCartHandler = async () => {
        if (isWithSpinbox && spinboxRef.current === null) {
            return;
        }

        const addedQuantity = isWithSpinbox ? spinboxRef.current!.valueAsNumber : 1;
        const addToCartResult = await addToCart(productUuid, addedQuantity, listIndex);

        if (isWithSpinbox) {
            spinboxRef.current!.valueAsNumber = 1;
        }

        if (addToCartResult) {
            updatePortalContent(
                <AddToCartPopup
                    key={addToCartResult.addProductResult.cartItem.uuid}
                    addedCartItem={addToCartResult.addProductResult.cartItem}
                />,
            );
        }
    };

    const quantity = isWithSpinbox ? spinboxRef.current?.valueAsNumber : 1;
    const ariaLabel = t('Add to cart {{ productName }}, quantity {{ quantity }} {{ unit }} for {{ price }}', {
        productName: ariaProductName,
        quantity,
        unit: ariaUnit,
        price: quantity ? formatPrice(quantity * mapPriceForCalculations(ariaPrice)) : undefined,
    });

    return (
        <div className={twMergeCustom('flex items-center justify-between gap-2', className)}>
            {isWithSpinbox && (
                <Spinbox
                    defaultValue={1}
                    id={productUuid}
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
                    isDisabled={isAddingToCart}
                    name="add-to-cart"
                    size={buttonSize}
                    tabIndex={tabIndex}
                    tid={TIDs.blocks_product_addtocart}
                    variant={buttonVariant}
                    onClick={onAddToCartHandler}
                >
                    {showResponsiveCartIcon && <CartIcon className="size-5 md:hidden" />}
                    <span className={showResponsiveCartIcon ? 'hidden md:block' : ''}>{t('Add to cart')}</span>
                </Button>
            </div>
        </div>
    );
};
