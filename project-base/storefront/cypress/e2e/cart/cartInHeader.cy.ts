import { openHeaderCartByMouseover, removeFirstProductFromHeaderCart } from './cartSupport';
import { products } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);

describe('Cart In Header Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.addProductToCartForTest(products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Cart Header Remove] should remove products from cart using cart in header and then display empty cart message', function () {
        openHeaderCartByMouseover();
        removeFirstProductFromHeaderCart();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after first remove', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.banners_slider, zIndex: 5999 },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.header_cart_list_item_image, zIndex: 10001 },
            ],
        });
        removeFirstProductFromHeaderCart();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after second remove', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider, zIndex: 5999 }, { tid: TIDs.simple_navigation_image }],
        });
    });
});
