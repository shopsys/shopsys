import { openHeaderCartByMouseover, removeFirstProductFromHeaderCart } from './cartSupport';
import { changeBlogArticleDynamicPartsToStaticDemodata } from 'e2e/visits/visitsSupport';
import { staticData } from 'fixtures/demodata';
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
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(staticData.products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Cart Header Remove] should remove products from cart using cart in header and then display empty cart message', function () {
        changeBlogArticleDynamicPartsToStaticDemodata();
        openHeaderCartByMouseover();
        removeFirstProductFromHeaderCart();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after first remove', {
            blackout: [
                { tid: TIDs.banners_slider, zIndex: 5999 },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.header_cart_list_item_image, zIndex: 10001 },
                { tid: TIDs.blog_preview_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
            preserveFixed: [TIDs.header_cart],
        });
        removeFirstProductFromHeaderCart();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after second remove', {
            wait: 2000,
            blackout: [
                { tid: TIDs.banners_slider, zIndex: 5999 },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.blog_preview_image },
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
            preserveFixed: [TIDs.header_cart],
        });
    });
});
