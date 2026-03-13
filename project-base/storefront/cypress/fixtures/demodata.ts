export const staticData = {
    brands: {
        sencor: 'Sencor',
    },
    user: {
        email: 'no-reply@shopsys.com',
        password: 'user123',
        uuid: '7b817d8b-41a3-4fc0-8570-08c9989f6dd9',
    },
    customer1: {
        email: 'no-reply123@shopsys.com',
        emailRegistered: 'no-reply@shopsys.com',
        firstName: 'jméno',
        lastName: 'příjmení',
        phone: '777666555',
        billingStreet: 'fakturační ulice 1/15a',
        billingCity: 'fakturační město',
        billingPostCode: '70200',
        password: 'user123',
    },
    deliveryAddress: {
        firstName: 'Janek',
        lastName: 'Zpodgrunia',
        company: 'Jankostaw',
        phone: '162736482',
        street: 'Jankowice 153a',
        city: 'Jankowice',
        postCode: '73961',
        country: 'CZ',
    },
    deliveryAddress2: {
        firstName: 'Tomáš',
        lastName: 'Marný',
        company: 'Márnice s.r.o',
        phone: '283918439',
        street: 'Marné Město 153/13b',
        city: 'Marné Město',
        postCode: '73963',
        country: 'CZ',
    },
    payment: {
        creditCard: { uuid: '808f7a8a-6143-538e-a46d-3803519ecf00' },
        cash: { uuid: '6e48952e-0f71-503c-8b58-f0ae9fc350c0' },
        onDelivery: { uuid: '2c3d2ede-bf1a-56bc-8c7d-44b7a672ef23' },
    },
    transport: {
        personalCollection: {
            uuid: 'b7461a55-b6e6-5b4f-a3c9-92f35366fe41',
            storeOstrava: {
                uuid: '4bade3e5-3b8c-5e71-93a5-ae1d84564575',
                name: 'Ostrava',
            },
            storePardubice: {
                uuid: '415ee258-8641-5ad5-bfa4-31eb2a75588a',
                name: 'Pardubice',
            },
        },
        czechPost: { uuid: 'f411f93c-6658-5bf6-ad68-cd69a83a88c0' },
        ppl: { uuid: '5d4619f7-a98f-5bff-b296-2a0887430a63' },
    },
    categories: {
        electronics: { uuid: '9808de4b-f953-544e-82ec-8417c2893cbd' },
        personalComputers: { uuid: 'e9129508-157a-5ae3-bba7-78632e0ee8fb' },
    } as const,
    products: {
        helloKitty: {
            uuid: '55bb22ab-bb88-5459-a464-005b948d8c78',
            name: '22" Sencor SLE 22F46DM4 HELLO KITTY',
            catnum: '9177759',
        },
        philips32PFL4308: {
            uuid: '7de699f8-bc41-5642-9ad8-3924a9d49f47',
            catnum: '9176508',
        },
        televisionPhilipsM: {
            uuid: 'f9c91468-c6b1-5b3f-b34e-1a2eef918504',
        },
        a4techMouse: {
            uuid: 'd5a669ed-46aa-5c55-b1fe-54e7b81de4cd',
            catnum: '5960453',
            name: 'A4tech mouse X-710BK, OSCAR Game, 2000DPI, black',
        },
        philips54CRT: {
            uuid: 'eff2bd27-7a46-5ccf-879c-915095bfb8fb',
            catnum: '9176588',
        },
        delonghi: {
            uuid: '7c476c2b-55a9-5195-9415-6a0874560a32',
            catnum: '9771339',
            name: 'DeLonghi ECAM 44.660 B Eletta Plus',
        },
        giftTicket100czk: {
            uuid: '7a1a818d-df2a-53e8-8c12-016fb4ca781e',
            catnum: '9176544MS',
            name: '100 Czech crowns ticket',
        },
    } as const,
    order: {
        number: '1234567890',
        numberHeading: 'Order number 1234567890',
        creationDate: '10/26/1999 10:10 AM',
    },
    blogArticle: {
        publicationDate: '10/26/1999',
        grapesJs: {
            uuid: 'cf500b03-d2e4-5549-b3af-cefed894c1b4',
        },
    },
    b2bOwner: {
        email: 'jozef.novotny@shopsys.com',
        password: 'user123',
    },
    b2bUser: {
        email: 'marek.horvat@shopsys.com',
        password: 'user123',
    },
    b2bLimitedUser: {
        email: 'peter.kovac@shopsys.com',
        password: 'user123',
    },
    b2bCatalogUser: {
        email: 'jiri.katalogovy@shopsys.com',
        password: 'user123',
    },
    b2bAccountant: {
        email: 'jana.ucetni@shopsys.com',
        password: 'user123',
    },
    promoCode: 'test',
    openingHours: '09:00 - 11:00, 13:00 - 17:00',
    openingStatus: 'Open',
    orderNote: 'Just a tiny note in the order.',
    smokeTestRoutesUuids: {
        category: '9808de4b-f953-544e-82ec-8417c2893cbd',
        product: '55bb22ab-bb88-5459-a464-005b948d8c78',
        blogCategory: 'f8129edb-3f92-5dfc-b6e5-261d85279485',
        blogArticle: 'fd418fec-fcd5-58ac-8e65-1fbbcd4260e3',
        brand: 'b077003c-3163-5126-890e-a2942355b17b',
        flag: '1d016b34-f541-576c-a135-e81aaba6be0e',
        store: '4bade3e5-3b8c-5e71-93a5-ae1d84564575',
        article: '6fc1a70f-b7a4-5f1b-850d-bc7520ef13e5',
    },
};

const { routes } = require('/config/routes');
const route = routes[0];

export const url = {
    cart: route['/cart'] as string,
    search: route['/search'] + '?q=',
    brandsOverview: route['/brands-overview'] as string,
    login: route['/login'] as string,
    registration: route['/registration'] as string,
    stores: route['/stores'] as string,
    contactForm: route['/contact-form'] as string,
    productComparison: route['/product-comparison'] as string,
    order: {
        transportAndPayment: route['/order/transport-and-payment'] as string,
        contactInformation: route['/order/contact-information'] as string,
        orderConfirmation: route['/order-confirmation'] as string,
        orderDetail: route['/order-detail/:urlHash']?.replace('/:urlHash', '') as string,
    },
    customer: {
        orders: route['/customer/orders'] as string,
        editProfile: route['/customer/edit-profile'] as string,
        complaints: route['/customer/complaints'] as string,
        newComplaint: route['/customer/new-complaint'] as string,
        complaintDetail: route['/customer/complaint-detail'] as string,
    },
};

type RouteMap = Record<string, string>;

const b2bDomainId = Cypress.env('B2B_DOMAIN_ID') ? Number(Cypress.env('B2B_DOMAIN_ID')) : null;
const b2bBaseUrl = (Cypress.env('B2B_BASE_URL') as string) || null;

// null when B2B is not configured — B2B test files check this and skip
export const b2bDomain =
    b2bDomainId && b2bBaseUrl ? { baseUrl: b2bBaseUrl, domainId: b2bDomainId, routes: routes[b2bDomainId - 1] } : null;

const b2bRoute: RouteMap = b2bDomain?.routes ?? {};

export const b2bUrl = {
    login: b2bRoute['/login'] as string,
    cart: b2bRoute['/cart'] as string,
    registration: b2bRoute['/registration'] as string,
    order: {
        transportAndPayment: b2bRoute['/order/transport-and-payment'] as string,
        contactInformation: b2bRoute['/order/contact-information'] as string,
        orderConfirmation: b2bRoute['/order-confirmation'] as string,
        orderDetail: b2bRoute['/order-detail/:urlHash']?.replace('/:urlHash', '') as string,
        orderWithdrawal: b2bRoute['/order-withdrawal/:orderUrlHash']?.replace('/:orderUrlHash', '') as string,
        orderWithdrawalSuccess: b2bRoute['/order-withdrawal-success/:orderUrlHash']?.replace(
            '/:orderUrlHash',
            '',
        ) as string,
    },
    customer: {
        users: b2bRoute['/customer/users'] as string,
        complaints: b2bRoute['/customer/complaints'] as string,
        newComplaint: b2bRoute['/customer/new-complaint'] as string,
        complaintDetail: b2bRoute['/customer/complaint-detail'] as string,
        orders: b2bRoute['/customer/orders'] as string,
        editProfile: b2bRoute['/customer/edit-profile'] as string,
        changePassword: b2bRoute['/customer/change-password'] as string,
    },
};

export const PERSIST_STORE_NAME = 'shopsys-platform-persist-store-1';
export const B2B_PERSIST_STORE_NAME = `shopsys-platform-persist-store-${b2bDomain?.domainId ?? 1}`;

export const DEFAULT_PERSIST_STORE_STATE = {
    state: {
        authLoading: null,
        cartUuid: null as string | null,
        productListUuids: {},
        userConsent: {
            statistics: false,
            marketing: false,
            preferences: false,
        },
        contactInformation: {
            email: '',
            telephone: '',
            firstName: '',
            lastName: '',
            street: '',
            city: '',
            postcode: '',
            customer: undefined,
            country: { value: '', label: '' },
            companyName: '',
            companyNumber: '',
            companyTaxNumber: '',
            isDeliveryAddressDifferentFromBilling: false,
            deliveryFirstName: '',
            deliveryLastName: '',
            deliveryCompanyName: '',
            deliveryTelephone: '',
            deliveryStreet: '',
            deliveryCity: '',
            deliveryPostcode: '',
            deliveryCountry: { value: '', label: '' },
            deliveryAddressUuid: '',
            newsletterSubscription: false,
            note: '',
            isWithoutHeurekaAgreement: false,
        },
        packeteryPickupPoint: null,
    },
    version: 1,
};
