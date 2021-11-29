import { ContactInformationFormType, CustomerTypeEnum } from 'components/Pages/Order/ContactInformation/formMeta';
import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { SelectOptionType } from 'components/Forms/Select/Select';

export const initialState = {
    email: '',
    register: false,
    passwordFirst: '',
    passwordSecond: '',
    customer: CustomerTypeEnum.CommonCustomer,
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
    newsletterSubscription: false,
} as ContactInformationFormType;

export const contactInformationSlice = createSlice({
    name: 'contactInformation',
    initialState,
    reducers: {
        resetContactInformation() {
            return initialState;
        },
        setContactInformation(state, action: PayloadAction<ContactInformationFormType>) {
            state.email = action.payload.email;
            state.register = action.payload.register;
            state.passwordFirst = action.payload.passwordFirst;
            state.passwordSecond = action.payload.passwordSecond;
            state.customer = action.payload.customer;
            state.telephone = action.payload.telephone;
            state.firstName = action.payload.firstName;
            state.lastName = action.payload.lastName;
            state.street = action.payload.street;
            state.city = action.payload.city;
            state.postcode = action.payload.postcode;
            state.country = action.payload.country;
            state.companyName = action.payload.companyName;
            state.companyNumber = action.payload.companyNumber;
            state.companyTaxNumber = action.payload.companyTaxNumber;
            state.differentDeliveryAddress = action.payload.differentDeliveryAddress;
            state.deliveryFirstName = action.payload.deliveryFirstName;
            state.deliveryLastName = action.payload.deliveryLastName;
            state.deliveryCompanyName = action.payload.deliveryCompanyName;
            state.deliveryTelephone = action.payload.deliveryTelephone;
            state.deliveryStreet = action.payload.deliveryStreet;
            state.deliveryCity = action.payload.deliveryCity;
            state.deliveryPostcode = action.payload.deliveryPostcode;
            state.deliveryCountry = action.payload.deliveryCountry;
            state.newsletterSubscription = action.payload.newsletterSubscription;
        },
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
        setCustomer(state, action: PayloadAction<CustomerTypeEnum>) {
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
        setCountry(state, action: PayloadAction<SelectOptionType>) {
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
        setDeliveryCountry(state, action: PayloadAction<SelectOptionType>) {
            state.deliveryCountry = action.payload;
        },
        setNewsletterSubscription(state, action: PayloadAction<boolean>) {
            state.newsletterSubscription = action.payload;
        },
        setDeliveryAddressFromPickupPlace(
            state,
            action: PayloadAction<{ street: string; city: string; postcode: string; country: SelectOptionType }>,
        ) {
            state.deliveryStreet = action.payload.street;
            state.deliveryCity = action.payload.city;
            state.deliveryPostcode = action.payload.postcode;
            state.deliveryCountry = action.payload.country;
        },
    },
});

export const contactInformationActions = contactInformationSlice.actions;
