import { TIDs } from 'tids';

export const checkGiftVoucherIsAppliedInCart = (giftVoucherCode: string) => {
    cy.getByTID([TIDs.blocks_promocode_giftvoucherinfo_code]).should('contain.text', giftVoucherCode);
    cy.getByTID([TIDs.cart_preview_remaining_amount_to_pay]).should('be.visible');
};

export const checkGiftVoucherIsNotAppliedInCart = () => {
    cy.getByTID([TIDs.blocks_promocode_giftvoucherinfo_code]).should('not.exist');
    cy.getByTID([TIDs.cart_preview_remaining_amount_to_pay]).should('not.exist');
};

export const removeGiftVoucherOnCartPage = () => {
    cy.getByTID([TIDs.blocks_promocode_giftvoucher_remove_button]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const checkEmailGiftVoucherInfoIsShownInsteadOfTransportList = () => {
    cy.getByTID([TIDs.pages_order_email_gift_voucher_info]).should('be.visible');
    cy.getByTID([TIDs.pages_order_transport]).find('input[name="transport"]').should('not.exist');
    cy.getByTID([TIDs.reset_transport_button]).should('not.exist');
};
