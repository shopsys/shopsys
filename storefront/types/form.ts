import { CustomerTypeEnum } from 'components/Pages/Order/ContactInformation/formMeta';
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
    newsletterSubscription: boolean;
};

export type PickupPlaceFormType = {
    pickupPlace: string;
};

export type TransportAndPaymentFormType = {
    transport: string | null;
    payment: string | null;
};

export type RegistrationAfterOrderFormType = {
    password: string;
    privacyPolicy: boolean;
};

export type PasswordResetFormType = {
    email: string;
};

export type NewPasswordFormType = {
    newPasswordFirst: string;
    newPasswordSecond: string;
};
