import { GtmConsentInfoType } from 'gtm/types/objects';
import { CustomerTypeEnum, DeliveryAddressType } from './customer';
import { SelectOptionType } from './selectOptions';

export type NewsletterFormType = {
    email: string;
    privacyPolicy: boolean;
};

export type RegistrationAfterOrderFormType = {
    password: string;
    passwordConfirm: string;
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
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
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

export type ChangePasswordFormType = {
    oldPassword: string;
    newPassword: string;
    newPasswordConfirm: string;
};

export type CustomerUserManageProfileFormType = {
    email: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephone: string;
    firstName: string;
    lastName: string;
    roleGroup: string;
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
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
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

export type OrderWithdrawalFormType = {
    firstName: string;
    lastName: string;
    email: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephone: string;
    note: string;
};

export type UserConsentFormType = Record<keyof GtmConsentInfoType, boolean>;

export type PromoCodeFormType = {
    promoCode: string;
};

export type LoginFormType = {
    email: string;
    password: string;
};

export type DeliveryAddressFormType = Omit<
    DeliveryAddressType,
    'uuid' | 'country' | 'telephoneNumber' | 'telephonePrefix' | 'telephonePrefixCountryCode'
> & {
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    country: SelectOptionType;
};

export type ComplaintFormType = {
    manualDocumentNumber: string | null;
    manualComplaintItemName: string | null;
    manualComplaintItemCatnum: string | null;
    email: string;
    quantity: string;
    description: string;
    files: File[];
    bankAccountNumber: string | null;
    deliveryAddressUuid: string | null;
    firstName: string;
    lastName: string;
    companyName: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephone: string;
    street: string;
    city: string;
    postcode: string;
    resolution: SelectOptionType;
    country: SelectOptionType;
};

export type InquiryFormType = {
    email: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephone: string;
    firstName: string;
    lastName: string;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    note: string;
    productUuid: string;
};

export type ProductQuestionFormType = {
    customerName: string;
    email: string;
    question: string;
    productUuid: string;
};

export type WatchdogFormType = {
    email: string;
    productUuid: string;
    gdprAgreement: boolean;
};
