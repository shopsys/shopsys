import { staticData } from 'fixtures/demodata';
import { TIDs } from 'tids';
import {
    TypeAddProductToListMutation,
    TypeAddProductToListMutationVariables,
} from '../../../graphql/requests/productLists/mutations/AddProductToListMutation.generated';

export const checkQueuedProductAdditions = (buttonTid: TIDs, listUrl: string, productTid: TIDs) => {
    const products = [staticData.products.helloKitty, staticData.products.philips32PFL4308];
    let requestCount = 0;
    let listUuid: string | undefined;
    let releaseFirstResponse!: () => void;
    const firstResponseGate = new Promise<void>((resolve) => {
        releaseFirstResponse = resolve;
    });

    cy.intercept('POST', '/graphql/AddProductToListMutation', (request) => {
        requestCount += 1;
        if (requestCount === 1) {
            // The second click must happen before the new list UUID reaches the browser.
            request.continue(() => firstResponseGate);
        }
    }).as('addProductToList');

    products.forEach((product, index) => {
        cy.getByTID([[TIDs.blocks_product_list_listeditem_, product.catnum], buttonTid])
            .should('be.visible')
            .click();
        if (index === 0) {
            cy.wrap(null).should(() => expect(requestCount).to.equal(1));
        }
    });
    cy.then(() => releaseFirstResponse());

    products.forEach((product, index) => {
        cy.wait<
            { variables: TypeAddProductToListMutationVariables },
            { data: TypeAddProductToListMutation; errors?: unknown[] }
        >('@addProductToList').then(({ request, response }) => {
            expect(request.body.variables.input.productUuid).to.equal(product.uuid);
            expect(request.body.variables.input.productListInput.uuid).to.equal(index === 0 ? null : listUuid);
            expect(response?.statusCode).to.equal(200);
            expect(response?.body.errors).to.be.undefined;

            const list = response?.body.data.AddProductToList;
            expect(list?.uuid).to.be.a('string').and.not.be.empty;
            expect(list?.itemsCount).to.equal(index + 1);
            expect(list?.productUuids).to.have.members(products.slice(0, index + 1).map(({ uuid }) => uuid));
            if (index === 0) {
                listUuid = list?.uuid;
            } else {
                expect(list?.uuid).to.equal(listUuid);
            }
        });
    });

    cy.visitAndWaitForStableAndInteractiveDOM(listUrl);
    products.forEach((product) => {
        cy.getByTID([[productTid, product.catnum]]).should('be.visible');
    });
    cy.get('@addProductToList.all').should('have.length', 2);
};
