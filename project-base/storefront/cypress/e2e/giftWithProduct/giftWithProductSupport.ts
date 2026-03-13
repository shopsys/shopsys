import { staticData } from 'fixtures/demodata';
import { t } from 'support/translations';
import { TIDs } from 'tids';

export const checkGiftIsDisplayedOnProductDetail = () => {
    t('Gifts').then((translatedText) => {
        cy.get('body').should('contain.text', translatedText);
    });
    t('Gift with purchase').then((translatedText) => {
        cy.get('body').should('contain.text', translatedText);
    });
};

export const checkGiftIsDisplayedInAddToCartPopup = () => {
    t('Gifts').then((translatedText) => {
        cy.getByTID([TIDs.layout_popup]).should('be.visible').and('contain.text', translatedText);
    });
    cy.getByTID([TIDs.layout_popup]).should('contain.text', staticData.products.giftTicket100czk.name);
};

export const checkGiftIsDisplayedInCart = () => {
    cy.getByTID([[TIDs.pages_cart_list_item_, staticData.products.giftTicket100czk.catnum]])
        .should('be.visible')
        .then(($el) => {
            t('Gift').then((translatedText) => {
                cy.wrap($el).should('contain.text', translatedText);
            });
        });
};

export const checkGiftIsDisplayedInOrderSummary = () => {
    t('Gift').then((translatedText) => {
        cy.getByTID([TIDs.order_summary_cart_item_image]).parent().closest('li').should('contain.text', translatedText);
    });
};

export const checkGiftIsDisplayedInOrderConfirmation = () => {
    t('Gift').then((translatedText) => {
        cy.getByTID([TIDs.pages_orderconfirmation]).should('contain.text', translatedText);
    });
    cy.getByTID([TIDs.pages_orderconfirmation]).should('contain.text', staticData.products.giftTicket100czk.name);
};

export const checkGiftIsDisplayedInOrderDetail = () => {
    t('Gift').then((translatedText) => {
        cy.getByTID([TIDs.order_detail_items]).should('contain.text', translatedText);
    });
    cy.getByTID([TIDs.order_detail_items]).should('contain.text', staticData.products.giftTicket100czk.name);
};
