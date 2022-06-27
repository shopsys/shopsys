import { CustomerTypeEnum } from './customer';
import { SelectOptionType } from './selectOptions';

export type PromoCodeFormType = {
    promoCode: string;
};

export type NewsletterFormType = {
    email: string;
    privacyPolicy: boolean;
};

export type AutocompleteSearchFormType = {
    autocompleteSearchQuery: string;
};

// EXTEND CUSTOMER CONTACT INFORMATION FORM HERE
export type ContactInformationFormType = {
    email: string;
    register: boolean;
    passwordFirst: string;
    passwordSecond: string;
    customer: CustomerTypeEnum;
    telephone: string;
    firstName: string;
    lastName: string;
    street: string;
    city: string;
    postcode: string;
    country: SelectOptionType;
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
    deliveryCountry: SelectOptionType;
    deliveryAddressUuid: string | null;
    newsletterSubscription: boolean;
    note: string;
};

export type PickupPlaceFormType = {
    pickupPlace: string;
};

export type TransportAndPaymentFormType = {
    transport: string | null;
    payment: string | null;
    goPaySwift: string | null;
};

export type RegistrationAfterOrderFormType = {
    password: string;
    privacyPolicy: boolean;
};

export type PasswordResetFormType = {
    email: string;
};

export type NewPasswordFormType = {
    newPassword: string;
    newPasswordAgain: string;
};

export type PersonalDataOverviewFormType = {
    email: string;
};

export type PersonalDataExportFormType = {
    email: string;
};

export type CustomerChangeProfileFormType = {
    companyCustomer: boolean;
    email: string;
    passwordOld: string;
    passwordFirst: string;
    passwordSecond: string;
    telephone: string;
    firstName: string;
    lastName: string;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    street: string;
    city: string;
    postcode: string;
    country: SelectOptionType;
    newsletterSubscription: boolean;
};

export type ContactFormType = {
    email: string;
    name: string;
    message: string;
};

export type UserConsentFormType = {
    functional: boolean;
    marketing: boolean;
    targeting: boolean;
    statistics: boolean;
    performance: boolean;
    preferences: boolean;
};
