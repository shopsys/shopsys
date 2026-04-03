import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { RefObject } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCart } from 'utils/cart/useAddToCart';

const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Popup/AddToCartPopup').then((component) => component.AddToCartPopup),
);

type UseAddToCartHandlerProps = {
    spinboxRef: RefObject<HTMLInputElement | null>;
    productUuid: string;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    isWithSpinbox?: boolean;
    listIndex?: number;
};

export const useAddToCartHandler = ({
    spinboxRef,
    productUuid,
    gtmMessageOrigin,
    gtmProductListName,
    isWithSpinbox = true,
    listIndex,
}: UseAddToCartHandlerProps) => {
    const { addToCart, isAddingToCart } = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onAddToCartHandler = async () => {
        if (isWithSpinbox && spinboxRef.current === null) {
            return;
        }

        const spinboxElement = spinboxRef.current;
        let addedQuantity = 1;

        if (isWithSpinbox && spinboxElement !== null) {
            addedQuantity = spinboxElement.valueAsNumber;
        }

        const addToCartResult = await addToCart(productUuid, addedQuantity, listIndex);

        if (isWithSpinbox && spinboxRef.current !== null) {
            spinboxRef.current.valueAsNumber = 1;
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

    return { onAddToCartHandler, isAddingToCart };
};
