export const brandSencor = 'Sencor';

export const buttonName = {
    login: 'Login',
};

export const customer1 = {
    email: 'no-reply123@shopsys.com',
    emailRegistered: 'no-reply@shopsys.com',
    firstName: 'jméno',
    lastName: 'příjmení',
    phone: '777666555',
    billingStreet: 'fakturační ulice 1/15a',
    billingCity: 'fakturační město',
    billingPostCode: '70200',
    password: 'user123',
};

export const link = {
    orderDetail: 'Track',
    myAccount: 'My account',
};

export const orderNote = 'Just a tiny note in the order.';

export const orderDetail = {
    numberHeading: 'Order number 1234567890',
    creationDate: '10/26/1999 10:10 AM',
};

export const payment = {
    creditCard: {
        uuid: 'a22b0dde-77ab-448f-be5e-831c0b2b5a32',
    },
    cash: {
        uuid: '7adc774b-aa39-4727-b373-544345814929',
        name: 'Cash',
    },
    onDelivery: {
        uuid: '1dd4fd71-3d82-48cb-b2b0-eecff0f297d3',
        name: 'Cash on delivery',
    },
};

export const placeholder = {
    password: 'Password',
    email: 'Your email',
    phone: 'Phone',
    firstName: 'First Name',
    lastName: 'Last Name',
    street: 'Street and house no.',
    city: 'City',
    postCode: 'Postcode',
    coupone: 'Coupon',
    note: 'Note',
};

export const products = {
    helloKitty: {
        uuid: '8c44b857-527d-41e2-a128-fc042b41736f',
        name: '22" Sencor SLE 22F46DM4 HELLO KITTY',
        fullName: 'Television 22" Sencor SLE 22F46DM4 HELLO KITTY plasma',
        catnum: 9177759,
        url: '/television-22-sencor-sle-22f46dm4-hello-kitty-plasma',
    },
    philips32PFL4308: {
        uuid: '5271462b-1d38-4a18-9d76-fbc06247c6f0',
        url: '/philips-32pfl4308',
    },
    philips54CRT: {
        name: '54" Philips CRT 32PFL4308',
        catnum: 9176588,
    },
} as const;

export const quantityUnit = 'pc';

export const transport = {
    personalCollection: {
        uuid: '45e4fe5a-db4a-49e8-80ec-5242a9858dce',
        name: 'Personal collection',
        storeOstrava: {
            uuid: '9be1392b-c39a-4130-a107-aedc56e7175e',
            name: 'Ostrava',
        },
    },
    czechPost: {
        uuid: 'c5bf95f7-0093-4345-96d9-562e9371a273',
        name: 'Czech post',
    },
    ppl: {
        uuid: 'ca676696-7fcf-43d8-a77e-9e9892cd464a',
        name: 'PPL',
    },
};

export const url = {
    cart: '/cart',
    search: '/search?q=',
    brandsOverwiev: '/brands-overview',
    order: {
        transportAndPayment: '/order/transport-and-payment',
        contactInformation: '/order/contact-information',
        orderConfirmation: '/order-confirmation',
        orderDetail: '/order-detail/',
    },
    login: '/login',
    loginWithCustomerRedirect: '/login?r=customer',
    customer: '/customer',
    categoryElectronics: '/electronics',
} as const;

export const DEFAULT_APP_STORE = {
    state: {
        loginLoading: null,
        cartUuid: null as string | null,
        comparisonUuid: null,
        contactInformation: {
            email: '',
            telephone: '',
            firstName: '',
            lastName: '',
            street: '',
            city: '',
            postcode: '',
            country: { value: '', label: '' },
            companyName: '',
            companyNumber: '',
            companyTaxNumber: '',
            differentDeliveryAddress: false,
            deliveryFirstName: '',
            deliveryLastName: '',
            deliveryCompanyName: '',
            deliveryTelephone: '',
            deliveryStreet: '',
            deliveryCity: '',
            deliveryPostcode: '',
            deliveryCountry: { value: '', label: '' },
            deliveryAddressUuid: null,
            newsletterSubscription: false,
            note: '',
        },
        packeteryPickupPoint: null,
        userConsent: {
            statistics: false,
            marketing: false,
            preferences: false,
        },
        wishlistUuid: null,
    },
    version: 0,
};
