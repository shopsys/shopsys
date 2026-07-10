import { TypeCreateOrderMutationVariables } from '../../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { TypePhoneDataInput, TypePromoCode, TypeRegistrationDataInput } from '../../graphql/types';
import 'cypress-real-events';
import { b2bDomain, PERSIST_STORE_NAME, staticData } from 'fixtures/demodata';

Cypress.Commands.add('checkGQL', { prevSubject: true }, (subject: Cypress.Response<any>, operationName: string) => {
    // Defer all work into Cypress chain so cy.log entries render in GUI before any throw
    return cy.wrap(subject, { log: false }).then((response) => {
        // Normalize body to object
        let body: any = response?.body;
        if (typeof body === 'string') {
            try {
                body = JSON.parse(body);
            } catch (e) {
                cy.log(`[GQL] ${operationName} non-JSON response (status ${response.status})`);
                return cy.then(() => {
                    throw new Error(
                        `[GQL] ${operationName} failed: response body is not valid JSON (status ${response.status})`,
                    );
                });
            }
        }

        const errors = body?.errors as
            | Array<{
                  message: string;
                  path?: (string | number)[];
                  extensions?:
                      | (Record<string, unknown> & {
                            code?: number | string;
                            userCode?: string | null;
                            validation?: Record<string, Array<{ code?: string; message?: string } | string>>;
                        })
                      | undefined;
              }>
            | undefined;
        const data = body?.data;

        if (Array.isArray(errors) && errors.length > 0) {
            const first = errors[0];
            const pathStr = first.path ? first.path.join('.') : undefined;

            const validation = first.extensions?.validation;
            const validationMessages: string[] = [];
            if (validation && typeof validation === 'object') {
                Object.entries(validation).forEach(([field, issues]) => {
                    const cleanField = field.startsWith('input.') ? field.replace(/^input\./, '') : field;
                    if (Array.isArray(issues)) {
                        for (const issue of issues) {
                            if (issue && typeof issue === 'object') {
                                const code = (issue as any).code ? ` [${(issue as any).code}]` : '';
                                const message = (issue as any).message ?? String(issue);
                                validationMessages.push(`${cleanField}: ${message}${code}`);
                            } else {
                                validationMessages.push(`${cleanField}: ${String(issue)}`);
                            }
                        }
                    }
                });
            }

            const extCode = first.extensions && (first.extensions as any).code;
            const userCode = first.extensions && (first.extensions as any).userCode;

            let detailedMessage = `${first.message}`;
            if (validationMessages.length > 0) {
                detailedMessage += `\nValidation:\n- ${validationMessages.join('\n- ')}`;
            }
            const summary = `${detailedMessage}${pathStr ? ` @ ${pathStr}` : ''}`;

            cy.log(
                `[GQL] ${operationName} failed (status ${response.status}${
                    extCode || userCode ? `, code ${extCode ?? ''}${userCode ? `, userCode ${userCode}` : ''}` : ''
                }):`,
            );
            if (validationMessages.length > 0) {
                cy.log(`Validation:\n- ${validationMessages.join('\n- ')}`);
            } else {
                cy.log(first.message);
            }

            return cy.then(() => {
                const codeInfo =
                    extCode || userCode ? `, code ${extCode ?? ''}${userCode ? `, userCode ${userCode}` : ''}` : '';
                throw new Error(`[GQL] ${operationName} failed: ${summary} (status ${response.status}${codeInfo})`);
            });
        }

        if (typeof data === 'undefined') {
            cy.log(`[GQL] ${operationName} missing both data and errors (status ${response.status})`);
            return cy.then(() => {
                throw new Error(
                    `[GQL] ${operationName} failed: missing both data and errors (status ${response.status})`,
                );
            });
        }

        return data;
    });
});

Cypress.Commands.add('addProductToCartForTest', (productUuid?: string, quantity?: number) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);

    return cy.getCookie('accessToken-1').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'AddToCartMutation',
                    query: `mutation AddToCartMutation($input: AddToCartInput!) {
                    AddToCart(input: $input) {
                        cart {
                            uuid
                        }
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            productUuid: productUuid ?? staticData.products.helloKitty.uuid,
                            quantity: quantity ?? 1,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
                failOnStatusCode: false,
            })
            .checkGQL('AddToCartMutation')
            .its('AddToCart.cart');
    });
});

Cypress.Commands.add('addPromoCodeToCartForTest', (promoCode: string) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);

    return cy.getCookie('accessToken-1').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'ApplyPromoCodeToCartMutation',
                    query: `mutation ApplyPromoCodeToCartMutation($input: ApplyPromoCodeToCartInput!) {
                    ApplyPromoCodeToCart(input: $input) {
                        uuid
                        promoCodes {
                            code
                        }
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            promoCode,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
                failOnStatusCode: false,
            })
            .checkGQL('ApplyPromoCodeToCartMutation')
            .its('ApplyPromoCodeToCart')
            .then((cart) => {
                expect(cart.uuid).equal(cartUuid);
                const responsePromoCode = cart.promoCodes.find(
                    (promoCodeLocal: TypePromoCode) => promoCodeLocal.code === promoCode,
                )?.code;
                expect(responsePromoCode).equal(promoCode);
            });
    });
});

Cypress.Commands.add('preselectTransportForTest', (transportUuid: string, pickupPlaceIdentifier?: string) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken-1').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'ChangeTransportInCartMutation',
                    query: `mutation ChangeTransportInCartMutation($input: ChangeTransportInCartInput!) {
                    ChangeTransportInCart(input: $input) {
                        uuid,
                        transport {
                            uuid
                        },
                        selectedPickupPlaceIdentifier
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            transportUuid,
                            pickupPlaceIdentifier,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
                failOnStatusCode: false,
            })
            .checkGQL('ChangeTransportInCartMutation')
            .its('ChangeTransportInCart')
            .then((cart) => {
                expect(cart.uuid).equal(cartUuid);
                expect(cart.transport.uuid).equal(transportUuid);
                if (pickupPlaceIdentifier) {
                    expect(cart.selectedPickupPlaceIdentifier).equal(pickupPlaceIdentifier);
                }
            });
    });
});

Cypress.Commands.add('preselectPaymentForTest', (paymentUuid: string) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken-1').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'ChangePaymentInCartMutation',
                    query: `mutation ChangePaymentInCartMutation($input: ChangePaymentInCartInput!) {
                    ChangePaymentInCart(input: $input) {
                        uuid,
                        payment {
                            uuid
                        }
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            paymentUuid,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
                failOnStatusCode: false,
            })
            .checkGQL('ChangePaymentInCartMutation')
            .its('ChangePaymentInCart')
            .then((cart) => {
                expect(cart.uuid).equal(cartUuid);
                expect(cart.payment.uuid).equal(paymentUuid);
            });
    });
});

Cypress.Commands.add('registerAsNewUser', (registrationInput: TypeRegistrationDataInput, shouldLogin = true) => {
    return cy
        .request({
            method: 'POST',
            url: 'graphql/',
            body: JSON.stringify({
                operationName: 'RegistrationMutation',
                query: `mutation RegistrationMutation($input: RegistrationDataInput!) {
                    Register(input: $input) {
                      tokens {
                        accessToken
                        refreshToken
                      }
                    }
                  }`,
                variables: {
                    input: registrationInput,
                },
            }),
            headers: {
                'Content-Type': 'application/json',
            },
            failOnStatusCode: false,
        })
        .checkGQL('RegistrationMutation')
        .its('Register')
        .then((registrationResponse) => {
            if (shouldLogin) {
                expect(registrationResponse.tokens.accessToken).to.be.a('string').and.not.be.empty;
                expect(registrationResponse.tokens.refreshToken).to.be.a('string').and.not.be.empty;
                cy.setCookie('accessToken-1', registrationResponse.tokens.accessToken, { path: '/' });
                cy.setCookie('refreshToken-1', registrationResponse.tokens.refreshToken, {
                    expiry: Math.floor(Date.now() / 1000) + 3600 * 24 * 14,
                    path: '/',
                });
            }
        });
});

let accessToken = null as string | null;
let refreshToken = null as string | null;
Cypress.Commands.add('login', (email = staticData.user.email, password = staticData.user.password) => {
    if (accessToken === null || refreshToken === null) {
        cy.request({
            method: 'POST',
            url: '/graphql/',
            headers: {
                'Content-Type': 'application/json',
            },
            body: {
                query: `mutation LoginMutation($email: String!, $password: Password!, $previousCartUuid: Uuid, $productListsUuids: [Uuid!]!) {
                        Login(
                            input: {email: $email, password: $password, cartUuid: $previousCartUuid, productListsUuids: $productListsUuids}
                        ) {
                            tokens {
                                accessToken
                                refreshToken
                                __typename
                            }
                            showCartMergeInfo
                            __typename
                        }
                    }
               `,
                variables: {
                    email,
                    password,
                    previousCartUuid: null,
                    productListsUuids: [],
                },
            },
            failOnStatusCode: false,
        })
            .checkGQL('LoginMutation')
            .then((data) => {
                accessToken = data.Login.tokens.accessToken;
                refreshToken = data.Login.tokens.refreshToken;

                cy.log('Customer login - ' + staticData.user.email);
                if (accessToken) {
                    cy.setCookie('accessToken-1', accessToken, { log: false });
                }
                if (refreshToken) {
                    cy.setCookie('refreshToken-1', refreshToken, { log: false });
                }
            });

        return;
    }

    cy.log('Customer login - ' + email);
    cy.setCookie('accessToken-1', accessToken, { log: false });
    cy.setCookie('refreshToken-1', refreshToken, { log: false });
});

Cypress.Commands.add('logout', () => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken-1').then((cookie) => {
        const accessToken = cookie?.value;

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'LogoutMutation',
                    query: `mutation LogoutMutation {
                    Logout
                }`,
                    variables: {},
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
                failOnStatusCode: false,
            })
            .checkGQL('LogoutMutation')
            .then((data) => {
                expect(data.Logout).to.be.true;
                cy.clearCookie('accessToken-1');
                cy.clearCookie('refreshToken-1');
            });
    });
});

Cypress.Commands.add('createOrder', (createOrderVariables: TypeCreateOrderMutationVariables) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken-1').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'CreateOrderMutation',
                    query: `mutation CreateOrderMutation(
                    $firstName: String!
                    $lastName: String!
                    $email: String!
                    $telephone: PhoneDataInput!
                    $onCompanyBehalf: Boolean!
                    $companyName: String
                    $companyNumber: String
                    $companyTaxNumber: String
                    $street: String!
                    $city: String!
                    $postcode: String!
                    $country: String!
                    $isDeliveryAddressDifferentFromBilling: Boolean!
                    $deliveryFirstName: String
                    $deliveryLastName: String
                    $deliveryCompanyName: String
                    $deliveryTelephone: PhoneDataInput
                    $deliveryStreet: String
                    $deliveryCity: String
                    $deliveryPostcode: String
                    $deliveryCountry: String
                    $deliveryAddressUuid: Uuid
                    $note: String
                    $cartUuid: Uuid
                    $newsletterSubscription: Boolean
                    $heurekaAgreement: Boolean!
                ) {
                    CreateOrder(
                        input: {
                            firstName: $firstName
                            lastName: $lastName
                            email: $email
                            telephone: $telephone
                            onCompanyBehalf: $onCompanyBehalf
                            companyName: $companyName
                            companyNumber: $companyNumber
                            companyTaxNumber: $companyTaxNumber
                            street: $street
                            city: $city
                            postcode: $postcode
                            country: $country
                            isDeliveryAddressDifferentFromBilling: $isDeliveryAddressDifferentFromBilling
                            deliveryFirstName: $deliveryFirstName
                            deliveryLastName: $deliveryLastName
                            deliveryCompanyName: $deliveryCompanyName
                            deliveryTelephone: $deliveryTelephone
                            deliveryStreet: $deliveryStreet
                            deliveryCity: $deliveryCity
                            deliveryPostcode: $deliveryPostcode
                            deliveryCountry: $deliveryCountry
                            deliveryAddressUuid: $deliveryAddressUuid
                            note: $note
                            cartUuid: $cartUuid
                            newsletterSubscription: $newsletterSubscription
                            heurekaAgreement: $heurekaAgreement
                        }
                    ) {
                        order {
                            urlHash
                    }
                }
            }`,
                    variables: { ...createOrderVariables, cartUuid },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
                failOnStatusCode: false,
            })
            .checkGQL('CreateOrderMutation')
            .its('CreateOrder.order');
    });
});

Cypress.Commands.add('createB2bOrderForTest', () => {
    const b2bGraphqlUrl = b2bDomain.baseUrl + '/graphql/';
    const b2bHostname = new URL(b2bDomain.baseUrl).hostname;
    const cookieName = `accessToken-${b2bDomain.domainId}`;
    type TransportForB2bOrder = {
        uuid: string;
        transportTypeCode: string;
        payments: Array<{ uuid: string }>;
    };

    return cy.getCookie(cookieName, { domain: b2bHostname }).then((cookie) => {
        const accessToken = cookie?.value;
        if (!accessToken) {
            throw new Error('B2B access token not found. Login as a B2B user before calling createB2bOrderForTest.');
        }

        const headers = {
            'Content-Type': 'application/json',
            'X-Auth-Token': 'Bearer ' + accessToken,
        };

        const gqlRequest = (operationName: string, query: string, variables: object) =>
            cy
                .request({
                    method: 'POST',
                    url: b2bGraphqlUrl,
                    body: JSON.stringify({ operationName, query, variables }),
                    headers,
                    failOnStatusCode: false,
                })
                .checkGQL(operationName);

        // Step 1: Query available transports to get a valid UUID for the B2B domain
        return gqlRequest(
            'TransportsQuery',
            `query TransportsQuery($cartUuid: Uuid) {
                transports(cartUuid: $cartUuid) {
                    uuid
                    transportTypeCode
                    payments { uuid }
                }
            }`,
            { cartUuid: null },
        ).then((data) => {
            const firstCommonTransport = (data.transports as TransportForB2bOrder[]).find(
                (transport) => transport.transportTypeCode === 'common',
            );
            if (!firstCommonTransport) {
                throw new Error('No common transports available on B2B domain.');
            }
            const transportUuid: string = firstCommonTransport.uuid;
            const paymentUuid: string = firstCommonTransport.payments[0]?.uuid;
            if (!paymentUuid) {
                throw new Error('No payments available for the selected B2B transport.');
            }

            // Step 2: AddToCart
            return gqlRequest(
                'AddToCartMutation',
                `mutation AddToCartMutation($input: AddToCartInput!) {
                    AddToCart(input: $input) {
                        cart { uuid }
                    }
                }`,
                { input: { productUuid: staticData.products.helloKitty.uuid, quantity: 1 } },
            ).then((addData) => {
                const cartUuid: string = addData.AddToCart.cart.uuid;

                // Step 3: ChangeTransport
                return gqlRequest(
                    'ChangeTransportInCartMutation',
                    `mutation ChangeTransportInCartMutation($input: ChangeTransportInCartInput!) {
                        ChangeTransportInCart(input: $input) { uuid transport { uuid } }
                    }`,
                    { input: { cartUuid, transportUuid } },
                ).then(() => {
                    // Step 4: ChangePayment
                    return gqlRequest(
                        'ChangePaymentInCartMutation',
                        `mutation ChangePaymentInCartMutation($input: ChangePaymentInCartInput!) {
                            ChangePaymentInCart(input: $input) { uuid payment { uuid } }
                        }`,
                        { input: { cartUuid, paymentUuid } },
                    ).then(() => {
                        // Step 5: CreateOrder
                        return gqlRequest(
                            'CreateOrderMutation',
                            `mutation CreateOrderMutation(
                                $cartUuid: Uuid
                                $firstName: String!
                                $lastName: String!
                                $telephone: PhoneDataInput!
                                $street: String!
                                $city: String!
                                $postcode: String!
                                $country: String!
                            ) {
                                CreateOrder(input: {
                                    cartUuid: $cartUuid
                                    firstName: $firstName
                                    lastName: $lastName
                                    telephone: $telephone
                                    street: $street
                                    city: $city
                                    postcode: $postcode
                                    country: $country
                                    onCompanyBehalf: false
                                    isDeliveryAddressDifferentFromBilling: false
                                    heurekaAgreement: false
                                    newsletterSubscription: false
                                }) {
                                    order { urlHash }
                                }
                            }`,
                            {
                                cartUuid,
                                firstName: 'Test',
                                lastName: 'B2B',
                                telephone: { countryCode: 'CZ', prefix: '+420', number: '777000111' },
                                street: 'Testovací 1',
                                city: 'Praha',
                                postcode: '10000',
                                country: 'CZ',
                            },
                        ).its('CreateOrder.order');
                    });
                });
            });
        });
    });
});

Cypress.Commands.add('loginB2b', (email: string, password: string) => {
    const b2bHostname = new URL(b2bDomain.baseUrl).hostname;

    cy.request({
        method: 'POST',
        url: b2bDomain.baseUrl + '/graphql/',
        headers: {
            'Content-Type': 'application/json',
        },
        body: {
            query: `mutation LoginMutation($email: String!, $password: Password!, $previousCartUuid: Uuid, $productListsUuids: [Uuid!]!) {
                Login(
                    input: {email: $email, password: $password, cartUuid: $previousCartUuid, productListsUuids: $productListsUuids}
                ) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                    showCartMergeInfo
                }
            }`,
            variables: {
                email,
                password,
                previousCartUuid: null,
                productListsUuids: [],
            },
        },
        failOnStatusCode: false,
    })
        .checkGQL('LoginMutation')
        .then((data) => {
            cy.log('B2B login - ' + email);
            cy.setCookie(`accessToken-${b2bDomain.domainId}`, data.Login.tokens.accessToken, {
                log: false,
                domain: b2bHostname,
            });
            cy.setCookie(`refreshToken-${b2bDomain.domainId}`, data.Login.tokens.refreshToken, {
                log: false,
                domain: b2bHostname,
            });
        });
});

const makeB2bGraphqlRequest = (operationName: string, query: string, variables: object) => {
    const b2bGraphqlUrl = b2bDomain.baseUrl + '/graphql/';
    const b2bHostname = new URL(b2bDomain.baseUrl).hostname;
    const cookieName = `accessToken-${b2bDomain.domainId}`;

    return cy.getCookie(cookieName, { domain: b2bHostname }).then((cookie) => {
        const token = cookie?.value;
        if (!token) {
            throw new Error('B2B access token not found. Login as a B2B user before calling this command.');
        }

        return cy
            .request({
                method: 'POST',
                url: b2bGraphqlUrl,
                body: JSON.stringify({ operationName, query, variables }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Auth-Token': 'Bearer ' + token,
                },
                failOnStatusCode: false,
            })
            .checkGQL(operationName);
    });
};

Cypress.Commands.add('getCustomerUserRoleGroupUuidForTest', () => {
    return makeB2bGraphqlRequest(
        'CustomerUserRoleGroupsQuery',
        `query CustomerUserRoleGroupsQuery {
            customerUserRoleGroups {
                uuid
                name
            }
        }`,
        {},
    ).then((data) => data.customerUserRoleGroups[0].uuid as string);
});

Cypress.Commands.add(
    'addCustomerUserViaApi',
    (input: {
        email: string;
        firstName: string;
        lastName: string;
        telephone: TypePhoneDataInput;
        roleGroupUuid: string;
        newsletterSubscription?: boolean;
    }) => {
        return makeB2bGraphqlRequest(
            'AddNewCustomerUserMutation',
            `mutation AddNewCustomerUserMutation($input: AddNewCustomerUserDataInput!) {
                AddNewCustomerUser(input: $input) {
                    uuid
                    firstName
                    lastName
                    email
                }
            }`,
            {
                input: {
                    email: input.email,
                    firstName: input.firstName,
                    lastName: input.lastName,
                    telephone: input.telephone,
                    roleGroupUuid: input.roleGroupUuid,
                    newsletterSubscription: input.newsletterSubscription ?? false,
                },
            },
        ).then(
            (data) => data.AddNewCustomerUser as { uuid: string; firstName: string; lastName: string; email: string },
        );
    },
);

Cypress.Commands.add('removeCustomerUserViaApi', (customerUserUuid: string) => {
    return makeB2bGraphqlRequest(
        'RemoveCustomerUserMutation',
        `mutation RemoveCustomerUserMutation($customerUserUuid: Uuid!) {
            RemoveCustomerUser(input: {customerUserUuid: $customerUserUuid})
        }`,
        { customerUserUuid },
    );
});

Cypress.Commands.add('removeCustomerUserByEmailIfExistsViaApi', (email: string) => {
    return makeB2bGraphqlRequest(
        'CurrentCustomerUsersQuery',
        `query CurrentCustomerUsersQuery {
            customerUsers {
                uuid
                email
            }
        }`,
        {},
    ).then((data) => {
        const user = data.customerUsers.find((u: { email: string }) => u.email === email);
        if (user) {
            return cy.removeCustomerUserViaApi(user.uuid);
        }
    });
});
