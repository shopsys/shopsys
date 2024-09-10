import { CustomerTypeEnum, DeliveryAddressType } from './customer';
import { SelectOptionType } from './selectOptions';
import { GtmConsentInfoType } from 'gtm/types/objects';

export type NewsletterFormType = {
    email: string;
    privacyPolicy: boolean;
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
    newPasswordConfirm: string;
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
    oldPassword: string;
    newPassword: string;
    newPasswordConfirm: string;
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
    privacyPolicy: boolean;
};

export type RegistrationFormType = {
    email: string;
    password: string;
    passwordConfirm: string;
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
    gdprAgreement: boolean;
    newsletterSubscription: boolean;
};

export type UserConsentFormType = Record<keyof GtmConsentInfoType, boolean>;

export type PromoCodeFormType = {
    promoCode: string;
};

export type LoginFormType = {
    email: string;
    password: string;
};

export type DeliveryAddressFormType = Omit<DeliveryAddressType, 'uuid' | 'country'> & {
    country: SelectOptionType;
};

export type ComplaintFormType = {
    quantity: string;
    description: string;
    files: File[];
    deliveryAddressUuid: string | null;
    firstName: string;
    lastName: string;
    companyName: string;
    telephone: string;
    street: string;
    city: string;
    postcode: string;
    country: SelectOptionType;
};
