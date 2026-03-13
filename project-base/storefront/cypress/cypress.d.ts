import { CreateOrderMutationVariables } from '../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { RegistrationDataInput } from '../graphql/types';
import { TIDs } from 'tids';

declare global {
    namespace Cypress {
        interface Chainable<Subject = any> {
            checkGQL<T = any>(operationName: string): Cypress.Chainable<T>;
            getByTID(
                value: ([TIDs, number | string] | TIDs)[],
                options?:
                    | Partial<Cypress.Loggable & Cypress.Timeoutable & Cypress.Withinable & Cypress.Shadow>
                    | undefined,
            ): Chainable<JQuery<HTMLElement>>;
            storeCartUuidInLocalStorage(cartUuid: string): Cypress.Chainable<undefined>;
            waitForStableAndInteractiveDOM(): Cypress.Chainable<JQuery<HTMLElement>>;
            visitAndWaitForStableAndInteractiveDOM(url: string): Cypress.Chainable<JQuery<HTMLElement>>;
            reloadAndWaitForStableAndInteractiveDOM(): Cypress.Chainable<JQuery<HTMLElement>>;
            visitB2bAndWaitForStableAndInteractiveDOM(
                path: string,
                options?: Partial<Cypress.VisitOptions>,
            ): Cypress.Chainable<JQuery<HTMLElement>>;
            waitForHydration(): Cypress.Chainable<void>;
            addProductToCartForTest(productUuid?: string, quantity?: number): Cypress.Chainable<any>;
            addPromoCodeToCartForTest(promoCode: string): Cypress.Chainable<any>;
            preselectTransportForTest(transportUuid: string, pickupPlaceIdentifier?: string): Cypress.Chainable<any>;
            preselectPaymentForTest(paymentUuid: string): Cypress.Chainable<any>;
            login(email?: string, password?: string): Cypress.Chainable<any>;
            loginB2b(email: string, password: string): Cypress.Chainable<any>;
            createB2bOrderForTest(): Cypress.Chainable<{ urlHash: string }>;
            getCustomerUserRoleGroupUuidForTest(): Cypress.Chainable<string>;
            addCustomerUserViaApi(input: {
                email: string;
                firstName: string;
                lastName: string;
                telephone: string;
                roleGroupUuid: string;
                newsletterSubscription?: boolean;
            }): Cypress.Chainable<{ uuid: string; firstName: string; lastName: string; email: string }>;
            removeCustomerUserViaApi(customerUserUuid: string): Cypress.Chainable<any>;
            removeCustomerUserByEmailIfExistsViaApi(email: string): Cypress.Chainable<any>;
            logout(): Cypress.Chainable<any>;
            createOrder(createOrderInput: CreateOrderMutationVariables): Cypress.Chainable<{ urlHash: string }>;
            registerAsNewUser(
                registrationInput: RegistrationDataInputApi,
                shouldLogin?: boolean,
            ): Cypress.Chainable<any>;

            setDevicePixelRatio(
                pixelRatio: number,
                options?: {
                    mobile: boolean;
                    width: number;
                    height: number;
                },
            ): void;
        }
    }
}

export {};
