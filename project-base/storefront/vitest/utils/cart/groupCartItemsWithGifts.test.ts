import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { groupCartItemsWithGifts } from 'utils/cart/groupCartItemsWithGifts';
import { describe, expect, test } from 'vitest';

type CartItem = TypeCartFragment['items'][number];
type RemovedGift = TypeCartFragment['removedProductGifts'][number];

const makeItem = (uuid: string, type = TypeCartItemTypeEnum.Product, relatedItems: CartItem[] = []): CartItem =>
    ({
        uuid: `${uuid}-item`,
        type,
        quantity: 3,
        product: { uuid, fullName: uuid },
        relatedItems: relatedItems.map((relatedItem) => ({ uuid: relatedItem.uuid })),
    }) as CartItem;

describe('groupCartItemsWithGifts', () => {
    test('groups a gift under the item it is related to and preserves item indexes', () => {
        const gift = makeItem('gift', TypeCartItemTypeEnum.ProductGift);
        const printer = makeItem('printer', TypeCartItemTypeEnum.Product, [gift]);
        const coffee = makeItem('coffee machine');
        const items = [printer, gift, coffee];

        const rows = groupCartItemsWithGifts(items, []);

        expect(rows.map((row) => [row.cartItem, row.listIndex])).toEqual([
            [printer, 0],
            [coffee, 2],
        ]);
        expect(rows[0].gifts).toEqual([
            {
                state: 'added',
                cartItem: gift,
                listIndex: 1,
                relatedProducts: [{ uuid: 'printer', fullName: 'printer' }],
            },
        ]);
        expect(rows[1].gifts).toEqual([]);
        expect(items).toEqual([printer, gift, coffee]);
    });

    test('groups gifts of the same product independently under each parent in either order', () => {
        const printerGift = {
            ...makeItem('gift', TypeCartItemTypeEnum.ProductGift),
            uuid: 'printer-gift',
            quantity: 5,
        };
        const coffeeGift = { ...makeItem('gift', TypeCartItemTypeEnum.ProductGift), uuid: 'coffee-gift', quantity: 3 };
        const beans = makeItem('beans', TypeCartItemTypeEnum.ProductGift);
        const printer = makeItem('printer', TypeCartItemTypeEnum.Product, [printerGift]);
        const coffee = makeItem('coffee', TypeCartItemTypeEnum.Product, [coffeeGift, beans]);

        for (const parents of [
            [printer, coffee],
            [coffee, printer],
        ]) {
            const rows = groupCartItemsWithGifts([...parents, printerGift, coffeeGift, beans], []);

            expect(rows.find((row) => row.cartItem === printer)?.gifts).toEqual([
                expect.objectContaining({ state: 'added', cartItem: printerGift }),
            ]);
            expect(rows.find((row) => row.cartItem === coffee)?.gifts).toEqual([
                expect.objectContaining({ state: 'added', cartItem: coffeeGift }),
                expect.objectContaining({ state: 'added', cartItem: beans }),
            ]);
        }
    });

    test('shows a removed gift under its main product while the same gift stays added for another', () => {
        const gift = makeItem('gift', TypeCartItemTypeEnum.ProductGift);
        const printer = makeItem('printer');
        const coffee = makeItem('coffee', TypeCartItemTypeEnum.Product, [gift]);
        const removed = { giftProduct: { uuid: 'gift' }, mainProduct: { uuid: 'printer' } } as RemovedGift;

        const rows = groupCartItemsWithGifts([coffee, gift, printer], [removed]);

        expect(rows[0].gifts).toEqual([expect.objectContaining({ state: 'added', cartItem: gift })]);
        expect(rows[1].gifts).toEqual([
            {
                state: 'removed',
                removedGift: removed,
                relatedProducts: [{ uuid: 'printer', fullName: 'printer' }],
            },
        ]);
    });

    test('keeps a gift without a parent item as a regular cart row', () => {
        const parent = makeItem('printer');
        const orphanGift = makeItem('gift', TypeCartItemTypeEnum.ProductGift);

        const rows = groupCartItemsWithGifts([parent, orphanGift], []);

        expect(rows.map((row) => row.cartItem)).toEqual([parent, orphanGift]);
        expect(rows[0].gifts).toEqual([]);
    });

    test('keeps a separately purchased gift product as a regular cart row', () => {
        const gift = { ...makeItem('gift', TypeCartItemTypeEnum.ProductGift), uuid: 'free-gift-item' };
        const parent = makeItem('printer', TypeCartItemTypeEnum.Product, [gift]);
        const purchasedGift = makeItem('gift');

        const rows = groupCartItemsWithGifts([parent, purchasedGift, gift], []);

        expect(rows.map((row) => row.cartItem)).toEqual([parent, purchasedGift]);
        expect(rows[0].gifts).toHaveLength(1);
        expect(rows[1].gifts).toEqual([]);
    });
});
