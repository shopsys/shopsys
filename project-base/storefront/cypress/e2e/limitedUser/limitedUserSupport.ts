import { TIDs } from 'tids';

export const visitB2bHomepageWithProducts = () => {
    cy.visitB2bAndWaitForStableAndInteractiveDOM('/');
};

export const checkPricesAreHidden = () => {
    cy.getByTID([TIDs.product_price]).should('not.exist');
};

export const checkPricesAreVisible = () => {
    cy.getByTID([TIDs.product_price]).should('exist');
};

export const checkAddToCartButtonNotVisible = () => {
    cy.getByTID([TIDs.blocks_product_addtocart]).should('not.exist');
};
