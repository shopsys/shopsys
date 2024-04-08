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
    isWithoutHeurekaAgreement: boolean;
};

const defaultContactInformation: ContactInformation = {
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
    isWithoutHeurekaAgreement: false,
};

export type ContactInformationSlice = {
    contactInformation: ContactInformation;
    updateContactInformation: (value: Partial<ContactInformation>) => void;
    resetContactInformation: () => void;
};

export const createContactInformationSlice: StateCreator<ContactInformationSlice> = (set) => ({
    contactInformation: defaultContactInformation,

    updateContactInformation: (value) => {
        set((store) => ({ ...store, contactInformation: { ...store.contactInformation, ...value } }));
    },
    resetContactInformation: () => {
        set({ contactInformation: defaultContactInformation });
    },
});
