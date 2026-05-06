import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { ProductInterfaceType } from 'types/product';

type ProductListAddGtmEventType = GtmEventType.add_to_wishlist | GtmEventType.add_to_comparison;
type ProductListRemoveGtmEventType = GtmEventType.remove_from_wishlist | GtmEventType.remove_from_comparison;

type ProductListGtmContextType = {
    product: ProductInterfaceType;
    gtmProductListName: GtmProductListNameType;
    listIndex?: number;
};

export const useProductListGtmEvent = (
    addEvent: ProductListAddGtmEventType,
    removeEvent: ProductListRemoveGtmEventType,
) => {
    const domainConfig = useDomainConfig();
    const { canSeePrices } = useAuthorization();
    const productListGtmContextRef = useRef(new Map<string, ProductListGtmContextType>());

    const clearProductListGtmContext = (productUuid: string) => {
        productListGtmContextRef.current.delete(productUuid);
    };

    const pushProductListGtmEvent = (
        event: ProductListAddGtmEventType | ProductListRemoveGtmEventType,
        productUuid: string,
    ) => {
        const productListGtmContext = productListGtmContextRef.current.get(productUuid);

        if (!productListGtmContext) {
            return;
        }

        clearProductListGtmContext(productUuid);

        import('gtm/handlers/onGtmChangeProductListItemEventHandler').then(
            ({ onGtmChangeProductListItemEventHandler }) => {
                onGtmChangeProductListItemEventHandler(
                    event,
                    productListGtmContext.product,
                    domainConfig,
                    productListGtmContext.listIndex,
                    productListGtmContext.gtmProductListName,
                    !canSeePrices,
                );
            },
        );
    };

    const toggleProductInListWithGtm = (
        product: ProductInterfaceType,
        gtmProductListName: GtmProductListNameType,
        listIndex: number | undefined,
        toggleProductInList: (productUuid: string) => boolean,
    ) => {
        const hasPendingGtmContext = productListGtmContextRef.current.has(product.uuid);

        if (!hasPendingGtmContext) {
            productListGtmContextRef.current.set(product.uuid, {
                product,
                gtmProductListName,
                listIndex,
            });
        }

        const didStartMutation = toggleProductInList(product.uuid);

        if (!didStartMutation && !hasPendingGtmContext) {
            clearProductListGtmContext(product.uuid);
        }
    };

    return {
        clearProductListGtmContext,
        pushAddProductListGtmEvent: (productUuid: string) => pushProductListGtmEvent(addEvent, productUuid),
        pushRemoveProductListGtmEvent: (productUuid: string) => pushProductListGtmEvent(removeEvent, productUuid),
        toggleProductInListWithGtm,
    };
};
