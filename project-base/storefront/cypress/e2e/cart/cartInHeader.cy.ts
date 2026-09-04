import { openHeaderCartByMouseover, removeProductFromHeaderCartWithSpinbox } from './cartSupport';
import { changeBlogArticleDynamicPartsToStaticDemodata } from 'e2e/visits/visitsSupport';
import { staticData } from 'fixtures/demodata';
import {
    checkNumberOfApiRequestsTriggeredByActions,
    getHeaderElementByTID,
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
        removeProductFromHeaderCartWithSpinbox(staticData.products.helloKitty.catnum, 2);
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
        removeProductFromHeaderCartWithSpinbox(staticData.products.philips32PFL4308.catnum, 1);
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

    it('[Cart Header Remove - Rapid Click] should send only one RemoveFromCart request when clicking rapidly', function () {
        openHeaderCartByMouseover();

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([
                    [TIDs.header_cart_list_item_, staticData.products.philips32PFL4308.catnum],
                    TIDs.forms_spinbox_decrease,
                ])
                    .should('be.visible')
                    .focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'RemoveFromCartMutation',
        );
    });

    it('[Cart Header Gift] should display gift item without remove controls', function () {
        cy.addProductToCartForTest(staticData.products.delonghi.uuid);
        cy.reloadAndWaitForStableAndInteractiveDOM();

        openHeaderCartByMouseover();

        cy.getByTID([[TIDs.header_cart_list_item_, staticData.products.giftTicket100czk.catnum]])
            .scrollIntoView()
            .should('be.visible')
            .and('contain.text', staticData.products.giftTicket100czk.name);
        cy.getByTID([
            [TIDs.header_cart_list_item_, staticData.products.giftTicket100czk.catnum],
            TIDs.pages_cart_removecartitembutton,
        ]).should('not.exist');
        cy.getByTID([
            [TIDs.header_cart_list_item_, staticData.products.giftTicket100czk.catnum],
            TIDs.forms_spinbox_decrease,
        ]).should('not.exist');
    });

    it('[Cart Header Hover] should switch between cart and customer menu popovers', function () {
        getHeaderElementByTID(TIDs.header_cart)
            .should('be.visible')
            .realHover()
            .should('have.attr', 'aria-expanded', 'true');
        cy.getByTID([TIDs.overlay]).should('be.visible');

        getHeaderElementByTID(TIDs.my_account_link)
            .should('be.visible')
            .realHover()
            .should('have.attr', 'aria-expanded', 'true');
        getHeaderElementByTID(TIDs.header_cart).should('have.attr', 'aria-expanded', 'false');
        cy.getByTID([TIDs.header, TIDs.my_account_link, TIDs.login_form]).filter(':visible').should('exist');

        getHeaderElementByTID(TIDs.header_cart)
            .realHover()
            .should('have.attr', 'aria-expanded', 'true');
        getHeaderElementByTID(TIDs.my_account_link).should('have.attr', 'aria-expanded', 'false');
        cy.getByTID([
            TIDs.header,
            TIDs.header_cart,
            [TIDs.header_cart_list_item_, staticData.products.helloKitty.catnum],
        ])
            .filter(':visible')
            .should('exist');
    });
});
