import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type CartItemQuantityControlsProps = {
    cartItem: TypeCartItemFragment;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex?: number;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    spinboxId?: string;
};

export const CartItemQuantityControls: FC<CartItemQuantityControlsProps> = ({
    cartItem,
    className,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    size = 'medium',
    spinboxId,
}) => {
    const spinboxRef = useRef<HTMLInputElement>(null);
    const confirmedQuantityRef = useRef(cartItem.quantity);
    const inFlightQuantityRef = useRef<number | null>(null);
    const lastSubmittedQuantityRef = useRef<number | null>(null);
    const queuedQuantityRef = useRef<number | null>(null);
    const [visibleQuantity, setVisibleQuantity] = useState(cartItem.quantity);
    const [quantityChangeAnnouncement, setQuantityChangeAnnouncement] = useState('');
    const { t } = useTranslation();
    const { addToCart, isAddingToCart } = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const { removeFromCart, isRemovingFromCart } = useRemoveFromCart(gtmProductListName);
    const { product } = cartItem;
    const maxQuantity = product.isAllowedNegativeStock ? null : product.stockQuantity;
    const hasPendingQuantityChange = visibleQuantity !== cartItem.quantity || isAddingToCart || isRemovingFromCart;

    const restorePreviousQuantity = useCallback((quantity: number) => {
        setVisibleQuantity(quantity);

        if (spinboxRef.current) {
            spinboxRef.current.valueAsNumber = quantity;
        }
    }, []);

    const onSubmitCartChange = useCallback(
        async (quantity: number) => {
            if (inFlightQuantityRef.current !== null) {
                queuedQuantityRef.current = quantity;

                return;
            }

            let quantityToSubmit: number | null = quantity;

            while (quantityToSubmit !== null) {
                const quantityToCompare = lastSubmittedQuantityRef.current ?? confirmedQuantityRef.current;

                if (quantityToSubmit === quantityToCompare) {
                    quantityToSubmit = null;

                    continue;
                }

                const submittedQuantity = quantityToSubmit;

                inFlightQuantityRef.current = submittedQuantity;
                lastSubmittedQuantityRef.current = submittedQuantity;
                queuedQuantityRef.current = null;

                const addToCartResult = await addToCart(product.uuid, submittedQuantity, listIndex, true);

                inFlightQuantityRef.current = null;

                if (!addToCartResult) {
                    restorePreviousQuantity(confirmedQuantityRef.current);
                    lastSubmittedQuantityRef.current = null;
                    queuedQuantityRef.current = null;

                    return;
                }

                setQuantityChangeAnnouncement(
                    t('Quantity of {{ productName }} updated to {{ quantity }} {{ unit }}', {
                        ns: 'accessibility',
                        productName: product.fullName,
                        quantity: submittedQuantity,
                        unit: product.unit.name,
                    }),
                );

                quantityToSubmit = queuedQuantityRef.current;
                queuedQuantityRef.current = null;
            }
        },
        [addToCart, listIndex, product.fullName, product.unit.name, product.uuid, restorePreviousQuantity, t],
    );

    const onRemoveFromCart = useCallback(async () => {
        const removeFromCartResult = await removeFromCart(cartItem, listIndex);

        if (!removeFromCartResult) {
            restorePreviousQuantity(cartItem.quantity);
        }
    }, [cartItem, listIndex, removeFromCart, restorePreviousQuantity]);

    useEffect(() => {
        if (lastSubmittedQuantityRef.current !== null && cartItem.quantity !== lastSubmittedQuantityRef.current) {
            return;
        }

        confirmedQuantityRef.current = cartItem.quantity;
        setVisibleQuantity(cartItem.quantity);
        lastSubmittedQuantityRef.current = null;
    }, [cartItem.quantity]);

    return (
        <div className={twMergeCustom('inline-flex w-full', className)}>
            <Spinbox
                defaultValue={visibleQuantity}
                decreaseAriaLabel={t('Decrease quantity of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                id={spinboxId ?? cartItem.uuid}
                increaseAriaLabel={t('Increase quantity of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                inputAriaLabel={t('Quantity of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                liveAnnouncement={quantityChangeAnnouncement}
                hasPendingLook={hasPendingQuantityChange}
                max={maxQuantity}
                min={1}
                minValueDecreaseAriaLabel={t('Remove from cart product {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
                minValueDecreaseIcon={<TrashCanIcon className="size-5" />}
                minValueDecreaseTitle={t('Remove from cart')}
                ref={spinboxRef}
                size={size}
                step={1}
                onChangeValueCallback={(quantity) => {
                    setVisibleQuantity(quantity);
                    onSubmitCartChange(quantity);
                }}
                onMinValueDecrease={onRemoveFromCart}
            />
        </div>
    );
};
