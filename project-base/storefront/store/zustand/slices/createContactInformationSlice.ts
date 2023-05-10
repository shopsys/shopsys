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
};

export type ContactInformationSlice = ContactInformation & {
    updateContactInformationState: (value: Partial<ContactInformationSlice>) => void;
    resetContactInformationState: () => void;
};

export const createContactInformationSlice: StateCreator<ContactInformationSlice> = (set) => ({
    ...defaultContactInformation,

    updateContactInformationState: (value) => {
        set(value);
    },
    resetContactInformationState: () => {
        set(defaultContactInformation);
    },
});
