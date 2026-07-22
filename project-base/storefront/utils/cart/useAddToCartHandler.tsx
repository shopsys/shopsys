import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { RefObject, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { OnProductAddedToCart, useAddToCart } from 'utils/cart/useAddToCart';

type UseAddToCartHandlerProps = {
    spinboxRef: RefObject<HTMLInputElement | null>;
    productUuid: string;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    isWithSpinbox?: boolean;
    listIndex?: number;
    onProductAddedToCart?: OnProductAddedToCart;
    onAddToCartFlowStateChange?: (isPending: boolean) => void;
};

export const useAddToCartHandler = ({
    spinboxRef,
    productUuid,
    gtmMessageOrigin,
    gtmProductListName,
    isWithSpinbox = true,
    listIndex,
    onProductAddedToCart,
    onAddToCartFlowStateChange,
}: UseAddToCartHandlerProps) => {
    const { addToCart, isAddingToCart } = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);
    const clearStoredFocus = useSessionStore((s) => s.clearStoredFocus);
    const [isAddToCartFlowPending, setIsAddToCartFlowPending] = useState(false);
    const isAddToCartFlowPendingRef = useRef(false);

    const onAddToCartHandler = async () => {
        if ((isWithSpinbox && spinboxRef.current === null) || isAddToCartFlowPendingRef.current) {
            return;
        }

        isAddToCartFlowPendingRef.current = true;
        setIsAddToCartFlowPending(true);
        onAddToCartFlowStateChange?.(true);
        const addToCartPopupComponentPromise = import('components/Blocks/Popup/AddToCartPopup');

        try {
            storeCurrentFocus();

            const spinboxElement = spinboxRef.current;
            let addedQuantity = 1;

            if (isWithSpinbox && spinboxElement !== null) {
                addedQuantity = spinboxElement.valueAsNumber;
            }

            const addToCartResult = await addToCart(productUuid, addedQuantity, listIndex, false, onProductAddedToCart);

            if (isWithSpinbox && spinboxRef.current !== null) {
                spinboxRef.current.valueAsNumber = 1;
            }

            if (addToCartResult) {
                const { AddToCartPopup } = await addToCartPopupComponentPromise;
                updatePortalContent(
                    <AddToCartPopup
                        key={addToCartResult.addProductResult.cartItem.uuid}
                        addedCartItem={addToCartResult.addProductResult.cartItem}
                    />,
                );
            } else {
                clearStoredFocus();
            }
        } finally {
            isAddToCartFlowPendingRef.current = false;
            setIsAddToCartFlowPending(false);
            onAddToCartFlowStateChange?.(false);
        }
    };

    return { onAddToCartHandler, isAddingToCart: isAddingToCart || isAddToCartFlowPending };
};
