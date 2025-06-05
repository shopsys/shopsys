export const brandSencor = 'Sencor';

export const openingHours = '09:00 - 11:00, 13:00 - 17:00';
export const buttonName = {
    login: 'Login',
};

export const password = 'user123';

export const user = {
    email: 'no-reply@shopsys.com',
    password: password,
    uuid: '7b817d8b-41a3-4fc0-8570-08c9989f6dd9',
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
    password,
};

export const deliveryAddress = {
    firstName: 'Janek',
    lastName: 'Zpodgrunia',
    company: 'Jankostaw',
    phone: '162736482',
    street: 'Jankowice 153a',
    city: 'Jankowice',
    postCode: '73961',
    country: 'CZ',
};

export const deliveryAddress2 = {
    firstName: 'Tomáš',
    lastName: 'Marný',
    company: 'Márnice s.r.o',
    phone: '283918439',
    street: 'Marné Město 153/13b',
    city: 'Marné Město',
    postCode: '73963',
    country: 'CZ',
};

export const link = {
    orderDetail: 'Track',
    myAccount: 'My account',
};

export const orderNote = 'Just a tiny note in the order.';

export const blogArticle = {
    publicationDate: '10/26/1999',
};

export const payment = {
    creditCard: {
        uuid: '808f7a8a-6143-538e-a46d-3803519ecf00',
        name: 'Credit card',
    },
    cash: {
        uuid: '6e48952e-0f71-503c-8b58-f0ae9fc350c0',
        name: 'Cash',
    },
    onDelivery: {
        uuid: '2c3d2ede-bf1a-56bc-8c7d-44b7a672ef23',
        name: 'Cash on delivery',
    },
    payLater: {
        name: 'Pay later',
    },
};

export const placeholder = {
    password: 'Password',
    passwordConfirm: 'Password again',
    email: 'Your email',
    phone: 'Phone',
    firstName: 'First Name',
    lastName: 'Last Name',
    street: 'Street and house no.',
    city: 'City',
    postCode: 'Postcode',
    coupone: 'Coupon',
    note: 'Note',
    company: 'Company',
    companyName: 'Company name',
    companyNumber: 'Company number',
    companyTaxNumber: 'Tax number',
    passwordAgain: 'Password again',
};

export const products = {
    helloKitty: {
        uuid: '55bb22ab-bb88-5459-a464-005b948d8c78',
        name: '22" Sencor SLE 22F46DM4 HELLO KITTY',
        fullName: 'Television 22" Sencor SLE 22F46DM4 HELLO KITTY plasma',
        catnum: '9177759',
        url: '/television-22-sencor-sle-22f46dm4-hello-kitty-plasma',
    },
    philips32PFL4308: {
        uuid: '7de699f8-bc41-5642-9ad8-3924a9d49f47',
        url: '/television-philips-m',
        catnum: '9176508',
    },
    a4techMouse: {
        uuid: 'd5a669ed-46aa-5c55-b1fe-54e7b81de4cd',
        catnum: '5960453',
    },
    philips54CRT: {
        uuid: 'eff2bd27-7a46-5ccf-879c-915095bfb8fb	',
        name: '54" Philips CRT [V]',
        catnum: '9176588',
    },
} as const;

export const promoCode = 'test';

export const transport = {
    personalCollection: {
        uuid: 'b7461a55-b6e6-5b4f-a3c9-92f35366fe41',
        name: 'Personal collection',
        storeOstrava: {
            uuid: '67ac2c38-7bdd-59fa-b762-0704cee8323e',
            name: 'Ostrava',
        },
        storePardubice: {
            uuid: 'c0b38c80-9755-5030-930b-fa971e03c4bd',
            name: 'Pardubice',
        },
    },
    czechPost: {
        uuid: 'f411f93c-6658-5bf6-ad68-cd69a83a88c0',
        name: 'Czech post',
    },
    ppl: {
        uuid: '5d4619f7-a98f-5bff-b296-2a0887430a63',
        name: 'PPL',
    },
    droneDelivery: {
        name: 'Drone delivery',
    },
};

export const order = {
    number: '1234567890',
    numberHeading: 'Order number 1234567890',
    creationDate: '10/26/1999 10:10 AM',
    confirmation: {
        presonalCollection: 'We are looking forward to your visit.',
        czechPost:
            'the Czech Post will try to deliver your parcel on time, but it will not succeed and despite the constant presence of your person at home, it will not catch you and you will have to pick up the parcel personally at the counter. Here, however, you have to endure an endlessly long line and an eternally grumpy lady postman.',
        packeta: 'Probably best value for your money',
        drone: 'Expect delivery by the end of next month',
        goPay: 'You have chosen GoPay Payment, you will be shown a payment gateway.',
        card: 'You have chosen payment by credit card. Please finish it in two business days.',
        orderCreatedText:
            'Order number 1234567890 has been sent, thank you for your purchase. We will contact you about next order status.',
    },
    detail: {
        products: {
            helloKitty: {
                ...products.helloKitty,
                price: '€139.96',
                quantity: '1 pcs',
                promoCode: 'Promo code -10%',
            },
        },
    },
};

export const url = {
    cart: '/cart',
    search: '/search?q=',
    brandsOverview: '/brands-overview',
    order: {
        transportAndPayment: '/order/transport-and-payment',
        contactInformation: '/order/contact-information',
        orderConfirmation: '/order-confirmation',
        orderDetail: '/order-detail',
    },
    login: '/login',
    loginWithCustomerRedirect: '/login?r=customer',
    customer: {
        orders: '/customer/orders',
        editProfile: '/customer/edit-profile',
    },
    categoryElectronics: '/electronics',
    categoryPersonalComputers: '/personal-computers-accessories',
    productHelloKitty: '/television-22-sencor-sle-22f46dm4-hello-kitty-plasma',
    registration: '/registration',
    stores: '/stores',
    blogArticleGrapesJs: '/grapesjs-page',
} as const;

export const PERSIST_STORE_NAME = 'shopsys-platform-persist-store';

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
