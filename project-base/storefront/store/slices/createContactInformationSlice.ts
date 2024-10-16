import { CustomerTypeEnum } from 'types/customer';
import { SelectOptionType } from 'types/selectOptions';
import { StateCreator } from 'zustand';

export type ContactInformation = {
    email: string;
    customer: CustomerTypeEnum | undefined;
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
    isDeliveryAddressDifferentFromBilling: boolean;
    deliveryFirstName: string;
    deliveryLastName: string;
    deliveryCompanyName: string;
    deliveryTelephone: string;
    deliveryStreet: string;
    deliveryCity: string;
    deliveryPostcode: string;
    deliveryCountry: SelectOptionType;
    deliveryAddressUuid: string;
    newsletterSubscription: boolean;
    note: string;
    isWithoutHeurekaAgreement: boolean;
};

type ContactInformationState = {
    contactInformation: ContactInformation;
};

export type ContactInformationSlice = ContactInformationState & {
    updateContactInformation: (value: Partial<ContactInformation>) => void;
    resetContactInformation: () => void;
};

export const defaultContactInformationState: ContactInformationState = {
    contactInformation: {
        email: '',
        customer: undefined,
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
};

export const createContactInformationSlice: StateCreator<ContactInformationSlice> = (set) => ({
    ...defaultContactInformationState,

    updateContactInformation: (value) => {
        set((store) => ({ ...store, contactInformation: { ...store.contactInformation, ...value } }));
    },
    resetContactInformation: () => {
        set({ ...defaultContactInformationState });
    },
});
