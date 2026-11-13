import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';

type CartItem = TypeCartFragment['items'][number];
type RemovedGift = TypeCartFragment['removedProductGifts'][number];

type GiftState =
    | { state: 'added'; cartItem: CartItem; listIndex: number }
    | { state: 'removed'; removedGift: RemovedGift };

export type CartProductGift = GiftState & {
    relatedProducts: Pick<CartItem['product'], 'uuid' | 'fullName'>[];
};

type CartProductRow = {
    cartItem: CartItem;
    listIndex: number;
    gifts: CartProductGift[];
};

export const groupCartItemsWithGifts = (
    cartItems: TypeCartFragment['items'],
    removedProductGifts: TypeCartFragment['removedProductGifts'],
): CartProductRow[] => {
    const rows = cartItems.map((cartItem, listIndex): CartProductRow => ({ cartItem, listIndex, gifts: [] }));
    const groupedGiftItemUuids = new Set<string>();

    const addGiftToRow = (gift: GiftState, parentRow?: CartProductRow): boolean => {
        if (!parentRow) {
            return false;
        }

        parentRow.gifts.push({
            ...gift,
            relatedProducts: [{ uuid: parentRow.cartItem.product.uuid, fullName: parentRow.cartItem.product.fullName }],
        });

        return true;
    };

    for (const { cartItem, listIndex } of rows) {
        if (cartItem.type !== TypeCartItemTypeEnum.ProductGift) {
            continue;
        }

        const parentRow = rows.find(({ cartItem: parentItem }) =>
            parentItem.relatedItems.some((relatedItem) => relatedItem.uuid === cartItem.uuid),
        );

        if (addGiftToRow({ state: 'added', cartItem, listIndex }, parentRow)) {
            groupedGiftItemUuids.add(cartItem.uuid);
        }
    }

    for (const removedGift of removedProductGifts) {
        addGiftToRow(
            { state: 'removed', removedGift },
            rows.find(
                ({ cartItem }) =>
                    cartItem.type === TypeCartItemTypeEnum.Product &&
                    cartItem.product.uuid === removedGift.mainProduct.uuid,
            ),
        );
    }

    return rows.filter(({ cartItem }) => !groupedGiftItemUuids.has(cartItem.uuid));
};
