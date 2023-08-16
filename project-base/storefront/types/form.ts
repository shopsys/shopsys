import { CustomerTypeEnum } from './customer';
import { GtmConsentInfoType } from '../gtm/types/objects';
import { SelectOptionType } from './selectOptions';

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

export type RegistrationFormType = {
    email: string;
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
    gdprAgreement: boolean;
    newsletterSubscription: boolean;
};

export type UserConsentFormType = Record<keyof GtmConsentInfoType, boolean>;
