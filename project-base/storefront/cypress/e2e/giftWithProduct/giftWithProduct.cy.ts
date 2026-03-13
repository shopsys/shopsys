import {
    checkGiftIsDisplayedInAddToCartPopup,
    checkGiftIsDisplayedInCart,
    checkGiftIsDisplayedInOrderConfirmation,
    checkGiftIsDisplayedInOrderDetail,
    checkGiftIsDisplayedInOrderSummary,
    checkGiftIsDisplayedOnProductDetail,
} from './giftWithProductSupport';
import { addToCartOnProductDetailPage } from 'e2e/cart/cartSupport';
import {
    changeOrderConfirmationDynamicPartsToStaticDemodata,
    changeOrderDetailDynamicPartsToStaticDemodata,
    clickOnOrderDetailButtonOnThankYouPage,
    clickOnSendOrderButton,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
} from 'e2e/order/orderSupport';
import { staticData, url } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.GIFT, SUBGROUP_INDEX);

describe('Gift with Product Tests (SSP-2756)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Product Detail & Cart] should display gift on product detail, add-to-cart popup, and cart page', () => {
        visitEntityByUuid('product', staticData.products.delonghi.uuid);
        checkGiftIsDisplayedOnProductDetail();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'product detail with gift', {
            blackout: [
                { tid: TIDs.product_detail_main_image },
                { tid: TIDs.add_to_cart_popup_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        addToCartOnProductDetailPage();
        cy.getByTID([TIDs.layout_popup]).should('be.visible');
        cy.waitForStableAndInteractiveDOM();
        checkGiftIsDisplayedInAddToCartPopup();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup with gift', {
            capture: 'viewport',
            blackout: [{ tid: TIDs.add_to_cart_popup_image, zIndex: 10002 }, { tid: TIDs.product_detail_main_image }],
            preserveFixed: [TIDs.layout_popup],
        });

        cy.getByTID([TIDs.popup_go_to_cart_button]).click();
        cy.url().should('contain', url.cart);
        cy.waitForStableAndInteractiveDOM();
        checkGiftIsDisplayedInCart();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page with gift', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Checkout Flow] should show gift in order summary, order confirmation, and order detail', () => {
        cy.addProductToCartForTest(staticData.products.delonghi.uuid, 1).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        checkGiftIsDisplayedInOrderSummary();
        fillEmailInThirdStep(staticData.customer1.email);
        fillCustomerInformationInThirdStep(
            staticData.customer1.phone,
            staticData.customer1.firstName,
            staticData.customer1.lastName,
        );
        fillBillingAdressInThirdStep(
            staticData.customer1.billingStreet,
            staticData.customer1.billingCity,
            staticData.customer1.billingPostCode,
        );
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information with gift in order summary', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        clickOnSendOrderButton();
        cy.url().should('contain', url.order.orderConfirmation);
        cy.waitForStableAndInteractiveDOM();
        checkGiftIsDisplayedInOrderConfirmation();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'order confirmation with gift', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        checkGiftIsDisplayedInOrderDetail();
        changeOrderDetailDynamicPartsToStaticDemodata(true);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'order detail with gift', {
            blackout: [
                { tid: TIDs.order_detail_item_image },
                { tid: TIDs.order_list_transport_and_payment_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
