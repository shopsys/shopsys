import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';

type CartItem = TypeCartFragment['items'][number];
type RemovedGift = TypeCartFragment['removedGifts'][number];

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
    removedGifts: TypeCartFragment['removedGifts'],
): CartProductRow[] => {
    const rows = cartItems.map((cartItem, listIndex): CartProductRow => ({ cartItem, listIndex, gifts: [] }));
    const groupedGiftItemUuids = new Set<string>();

    const addGiftToProduct = (gift: GiftState, productUuid: string): boolean => {
        const relatedRows = rows.filter(
            ({ cartItem }) =>
                cartItem.type === TypeCartItemTypeEnum.Product &&
                cartItem.product.gifts.some((productGift) => productGift.uuid === productUuid),
        );
        const parentRow = relatedRows[0];

        if (!parentRow) {
            return false;
        }

        parentRow.gifts.push({
            ...gift,
            relatedProducts: relatedRows.map(({ cartItem }) => ({
                uuid: cartItem.product.uuid,
                fullName: cartItem.product.fullName,
            })),
        });

        return true;
    };

    for (const { cartItem, listIndex } of rows) {
        if (
            cartItem.type === TypeCartItemTypeEnum.ProductGift &&
            addGiftToProduct({ state: 'added', cartItem, listIndex }, cartItem.product.uuid)
        ) {
            groupedGiftItemUuids.add(cartItem.uuid);
        }
    }

    for (const removedGift of removedGifts) {
        addGiftToProduct({ state: 'removed', removedGift }, removedGift.product.uuid);
    }

    return rows.filter(({ cartItem }) => !groupedGiftItemUuids.has(cartItem.uuid));
};
