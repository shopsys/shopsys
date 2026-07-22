import { useProductAdditionalServicesSelectionContext } from 'components/providers/ProductAdditionalServicesSelectionProvider';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useState } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useSetCartItemAdditionalServices } from 'utils/cart/useSetCartItemAdditionalServices';

type UseProductAdditionalServicesProps = {
    productUuid: string;
    gtmProductListName: GtmProductListNameType;
    knownCartItem?: TypeCartItemFragment;
};

export const useProductAdditionalServices = ({
    productUuid,
    gtmProductListName,
    knownCartItem,
}: UseProductAdditionalServicesProps) => {
    const { cart } = useCurrentCart();
    const { setCartItemAdditionalServices, isSettingAdditionalServices } =
        useSetCartItemAdditionalServices(gtmProductListName);
    const productAdditionalServicesSelectionContext = useProductAdditionalServicesSelectionContext();
    const [localPendingServiceUuids, setLocalPendingServiceUuids] = useState<string[]>([]);
    const [localIsAddToCartFlowPending, setLocalIsAddToCartFlowPending] = useState(false);
    const pendingServiceUuids =
        productAdditionalServicesSelectionContext?.pendingServiceUuidsByProductUuid[productUuid] ??
        localPendingServiceUuids;
    const isAddToCartFlowPending =
        productAdditionalServicesSelectionContext?.isAddToCartFlowPendingByProductUuid[productUuid] ??
        localIsAddToCartFlowPending;

    const updatePendingServiceUuids = (updater: (serviceUuids: string[]) => string[]) => {
        if (productAdditionalServicesSelectionContext) {
            productAdditionalServicesSelectionContext.updatePendingServiceUuids(productUuid, updater);

            return;
        }

        setLocalPendingServiceUuids(updater);
    };

    const updateIsAddToCartFlowPending = (isPending: boolean) => {
        if (productAdditionalServicesSelectionContext) {
            productAdditionalServicesSelectionContext.setIsAddToCartFlowPending(productUuid, isPending);

            return;
        }

        setLocalIsAddToCartFlowPending(isPending);
    };

    const cartItem =
        knownCartItem ??
        cart?.items.find((item) => item.type === TypeCartItemTypeEnum.Product && item.product.uuid === productUuid);

    const selectedServiceUuids =
        cartItem && !isAddToCartFlowPending
            ? cartItem.additionalServices.map((additionalService) => additionalService.uuid)
            : pendingServiceUuids;

    const onToggleService = (additionalService: TypeAdditionalServiceFragment, isSelected: boolean) => {
        if (cartItem) {
            const updatedServiceUuids = isSelected
                ? [...selectedServiceUuids, additionalService.uuid]
                : selectedServiceUuids.filter((serviceUuid) => serviceUuid !== additionalService.uuid);

            setCartItemAdditionalServices(cartItem.uuid, updatedServiceUuids, {
                cartItem,
                toggledService: additionalService,
                isServiceAdded: isSelected,
            });

            return;
        }

        updatePendingServiceUuids((previousServiceUuids) =>
            isSelected
                ? [...previousServiceUuids, additionalService.uuid]
                : previousServiceUuids.filter((serviceUuid) => serviceUuid !== additionalService.uuid),
        );
    };

    const persistPendingServicesAfterAddToCart = async (
        addedCartItemUuid: string,
    ): Promise<TypeCartFragment | null> => {
        if (cartItem || pendingServiceUuids.length === 0) {
            return null;
        }

        const updatedCart = await setCartItemAdditionalServices(addedCartItemUuid, pendingServiceUuids);

        if (updatedCart) {
            if (productAdditionalServicesSelectionContext) {
                productAdditionalServicesSelectionContext.clearPendingServiceUuids(productUuid);
            } else {
                setLocalPendingServiceUuids([]);
            }
        }

        return updatedCart;
    };

    return {
        cartItem,
        isAddToCartFlowPending,
        selectedServiceUuids,
        updateIsAddToCartFlowPending,
        onToggleService,
        persistPendingServicesAfterAddToCart,
        isSettingAdditionalServices,
        cartItemQuantity: cartItem?.quantity,
    };
};
