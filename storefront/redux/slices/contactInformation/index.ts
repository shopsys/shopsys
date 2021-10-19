import { createSlice, PayloadAction } from '@reduxjs/toolkit';

export type CustomerType = 'commonCustomer' | 'companyCustomer';

export type ContactInformationFormType = {
    email: string;
    register: boolean;
    passwordFirst: string;
    passwordSecond: string;
    customer: CustomerType;
    telephone: string;
    firstName: string;
    lastName: string;
    street: string;
    city: string;
    postcode: string;
    country: string;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    differentDeliveryAddress: boolean;
    deliveryFirstName: string;
    deliveryLastName: string;
    deliveryCompanyName: string;
    deliveryTelephone: string;
    deliveryStreet: string;
    deliveryCity: string;
    deliveryPostcode: string;
    deliveryCountry: string;
    newsletterSubscription: boolean;
};

export const initialState = {
    email: '',
    register: false,
    passwordFirst: '',
    passwordSecond: '',
    customer: 'commonCustomer',
    telephone: '',
    firstName: '',
    lastName: '',
    street: '',
    city: '',
    postcode: '',
    country: 'CZ',
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
    deliveryCountry: 'CZ',
    newsletterSubscription: false,
} as ContactInformationFormType;

export const contactInformationSlice = createSlice({
    name: 'contactInformation',
    initialState,
    reducers: {
        setEmail(state, action: PayloadAction<string>) {
            state.email = action.payload;
        },
        setRegister(state, action: PayloadAction<boolean>) {
            state.register = action.payload;
        },
        setPasswordFirst(state, action: PayloadAction<string>) {
            state.passwordFirst = action.payload;
        },
        setPasswordSecond(state, action: PayloadAction<string>) {
            state.passwordSecond = action.payload;
        },
        setCustomer(state, action: PayloadAction<CustomerType>) {
            state.customer = action.payload;
        },
        setTelephone(state, action: PayloadAction<string>) {
            state.telephone = action.payload;
        },
        setFirstName(state, action: PayloadAction<string>) {
            state.firstName = action.payload;
        },
        setLastName(state, action: PayloadAction<string>) {
            state.lastName = action.payload;
        },
        setStreet(state, action: PayloadAction<string>) {
            state.street = action.payload;
        },
        setCity(state, action: PayloadAction<string>) {
            state.city = action.payload;
        },
        setPostcode(state, action: PayloadAction<string>) {
            state.postcode = action.payload;
        },
        setCountry(state, action: PayloadAction<string>) {
            state.country = action.payload;
        },
        setCompanyName(state, action: PayloadAction<string>) {
            state.companyName = action.payload;
        },
        setCompanyNumber(state, action: PayloadAction<string>) {
            state.companyNumber = action.payload;
        },
        setCompanyTaxNumber(state, action: PayloadAction<string>) {
            state.companyTaxNumber = action.payload;
        },
        setDifferentDeliveryAddress(state, action: PayloadAction<boolean>) {
            state.differentDeliveryAddress = action.payload;
        },
        setDeliveryFirstName(state, action: PayloadAction<string>) {
            state.deliveryFirstName = action.payload;
        },
        setDeliveryLastName(state, action: PayloadAction<string>) {
            state.deliveryLastName = action.payload;
        },
        setDeliveryCompanyName(state, action: PayloadAction<string>) {
            state.deliveryCompanyName = action.payload;
        },
        setDeliveryTelephone(state, action: PayloadAction<string>) {
            state.deliveryTelephone = action.payload;
        },
        setDeliveryStreet(state, action: PayloadAction<string>) {
            state.deliveryStreet = action.payload;
        },
        setDeliveryCity(state, action: PayloadAction<string>) {
            state.deliveryCity = action.payload;
        },
        setDeliveryPostcode(state, action: PayloadAction<string>) {
            state.deliveryPostcode = action.payload;
        },
        setDeliveryCountry(state, action: PayloadAction<string>) {
            state.deliveryCountry = action.payload;
        },
        setNewsletterSubscription(state, action: PayloadAction<boolean>) {
            state.newsletterSubscription = action.payload;
        },
    },
});

export const contactInformationActions = contactInformationSlice.actions;
