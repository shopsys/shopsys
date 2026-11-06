import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { groupCartItemsWithGifts } from 'utils/cart/groupCartItemsWithGifts';
import { describe, expect, test } from 'vitest';

type CartItem = TypeCartFragment['items'][number];
type RemovedGift = TypeCartFragment['removedGifts'][number];

const makeItem = (uuid: string, giftUuids: string[] = [], type = TypeCartItemTypeEnum.Product): CartItem =>
    ({
        uuid: `${uuid}-item`,
        type,
        quantity: 3,
        product: { uuid, fullName: uuid, gifts: giftUuids.map((giftUuid) => ({ uuid: giftUuid })) },
    }) as CartItem;

describe('groupCartItemsWithGifts', () => {
    test('preserves the item and index of a legacy gift without an explicit parent', () => {
        const firstProduct = makeItem('printer', ['gift']);
        const gift = makeItem('gift', [], TypeCartItemTypeEnum.ProductGift);
        const secondProduct = makeItem('coffee machine', ['gift']);
        const items = [firstProduct, gift, secondProduct];

        const rows = groupCartItemsWithGifts(items, []);

        expect(rows.map((row) => [row.cartItem, row.listIndex])).toEqual([
            [firstProduct, 0],
            [secondProduct, 2],
        ]);
        expect(rows[0].gifts).toEqual([
            {
                state: 'added',
                cartItem: gift,
                listIndex: 1,
                relatedProducts: [
                    { uuid: 'printer', fullName: 'printer' },
                    { uuid: 'coffee machine', fullName: 'coffee machine' },
                ],
            },
        ]);
        expect(rows[0].gifts[0].state === 'added' && rows[0].gifts[0].cartItem).toBe(gift);
        expect(rows[1].gifts).toEqual([]);
        expect(items).toEqual([firstProduct, gift, secondProduct]);
    });

    test('keeps a legacy removed gift under its first qualifying product', () => {
        const products = [makeItem('printer', ['gift']), makeItem('coffee machine', ['gift'])];
        const removedGift = {
            product: { uuid: 'gift', fullName: 'gift' },
            quantity: 2,
        } as TypeCartFragment['removedGifts'][number];

        const rows = groupCartItemsWithGifts(products, [removedGift]);

        expect(rows[0].gifts).toEqual([
            {
                state: 'removed',
                removedGift,
                relatedProducts: [
                    { uuid: 'printer', fullName: 'printer' },
                    { uuid: 'coffee machine', fullName: 'coffee machine' },
                ],
            },
        ]);
        expect(rows[1].gifts).toEqual([]);
    });

    test('groups shared gifts independently using the server parent and quantity in either insertion order', () => {
        const printer = makeItem('printer', ['gift']);
        const coffee = makeItem('coffee', ['gift', 'beans']);
        const printerGift = {
            ...makeItem('gift', [], TypeCartItemTypeEnum.ProductGift),
            uuid: 'printer-gift',
            giftMainProductUuid: 'printer',
            quantity: 5,
        };
        const coffeeGift = {
            ...makeItem('gift', [], TypeCartItemTypeEnum.ProductGift),
            uuid: 'coffee-gift',
            giftMainProductUuid: 'coffee',
            quantity: 3,
        };
        const beans = {
            ...makeItem('beans', [], TypeCartItemTypeEnum.ProductGift),
            giftMainProductUuid: 'coffee',
            quantity: 3,
        };

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

    test('shows one removed pair under its parent while the shared gift stays selected for another', () => {
        const printer = makeItem('printer');
        const coffee = makeItem('coffee');
        const gift = {
            ...makeItem('gift', [], TypeCartItemTypeEnum.ProductGift),
            giftMainProductUuid: 'coffee',
        };
        const removed = { product: { uuid: 'gift' }, quantity: 5, mainProductUuid: 'printer' } as RemovedGift;

        const rows = groupCartItemsWithGifts([coffee, gift, printer], [removed]);

        expect(rows[0].gifts).toEqual([expect.objectContaining({ state: 'added', cartItem: gift })]);
        expect(rows[1].gifts).toEqual([expect.objectContaining({ state: 'removed', removedGift: removed })]);
    });

    test('moves a shared gift to the remaining eligible product when its first owner disappears', () => {
        const remainingProduct = makeItem('coffee machine', ['gift']);
        const gift = makeItem('gift', [], TypeCartItemTypeEnum.ProductGift);

        const rows = groupCartItemsWithGifts([gift, remainingProduct], []);

        expect(rows).toHaveLength(1);
        expect(rows[0].cartItem).toBe(remainingProduct);
        expect(rows[0].gifts[0].relatedProducts).toEqual([{ uuid: 'coffee machine', fullName: 'coffee machine' }]);
    });

    test('keeps a separately purchased gift product as a regular cart row', () => {
        const parent = makeItem('printer', ['gift']);
        const purchasedGift = makeItem('gift');
        const gift = { ...makeItem('gift', [], TypeCartItemTypeEnum.ProductGift), uuid: 'free-gift-item' };

        const rows = groupCartItemsWithGifts([parent, purchasedGift, gift], []);

        expect(rows.map((row) => row.cartItem)).toEqual([parent, purchasedGift]);
        expect(rows[0].gifts).toHaveLength(1);
        expect(rows[1].gifts).toEqual([]);
    });

    test('keeps an actual gift item visible when product metadata does not identify a parent', () => {
        const parent = makeItem('printer');
        const unmatchedGift = makeItem('gift', [], TypeCartItemTypeEnum.ProductGift);

        const rows = groupCartItemsWithGifts([parent, unmatchedGift], []);

        expect(rows.map((row) => row.cartItem)).toEqual([parent, unmatchedGift]);
    });
});
