import {
    CurrentCustomerUserQuery,
    useCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useCountriesAsSelectOptions } from 'hooks/countries/useCountriesAsSelectOptions';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { CustomerTypeEnum } from 'types/customer';
import { SelectOptionType } from 'types/selectOptions';

export const useCurrentUserContactInformation = (): ContactInformation => {
    const [{ data }] = useCurrentCustomerUserQuery();
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    const contactInformationApiData = mapCurrentCustomerContactInformationApiData(data?.currentCustomerUser);
    const contactInformationFromStore = usePersistStore((store) => store.contactInformation);

    if (!contactInformationApiData) {
        const contactInformation = {
            ...contactInformationFromStore,
        };
        assertCountries(contactInformation, countriesAsSelectOptions);
        assertCustomer(contactInformation);

        return contactInformation;
    }

    return mergeContactInformation(contactInformationApiData, contactInformationFromStore, countriesAsSelectOptions);
};

const mergeContactInformation = (
    contactInformationFromApi: Partial<ContactInformation>,
    contactInformationFromStore: ContactInformation,
    countriesAsSelectOptions: SelectOptionType[],
): ContactInformation => {
    const filteredContactInformationFromStore: ContactInformation = {
        ...contactInformationFromStore,
    };

    for (const key in filteredContactInformationFromStore) {
        const filteredProperty = filteredContactInformationFromStore[key as keyof ContactInformation];

        const isEmptyString = typeof filteredProperty === 'string' && filteredProperty.length === 0;
        if ((isEmptyString || filteredProperty === undefined) && key in contactInformationFromApi) {
            delete filteredContactInformationFromStore[key as keyof ContactInformation];
        }
    }

    const contactInformation = {
        ...contactInformationFromApi,
        ...filteredContactInformationFromStore,
    };
    assertCountries(contactInformation, countriesAsSelectOptions);
    assertCustomer(contactInformation);

    return contactInformation;
};

const assertCustomer = (contactInformation: ContactInformation) => {
    contactInformation.customer = contactInformation.customer ?? CustomerTypeEnum.CommonCustomer;
};

const assertCountries = (contactInformation: ContactInformation, countriesAsSelectOptions: SelectOptionType[]) => {
    if (countriesAsSelectOptions.length > 0) {
        contactInformation.country =
            contactInformation.country.value.length > 0 ? contactInformation.country : countriesAsSelectOptions[0];
        contactInformation.deliveryCountry =
            contactInformation.deliveryCountry.value.length > 0
                ? contactInformation.deliveryCountry
                : countriesAsSelectOptions[0];
    }
};

const mapCurrentCustomerContactInformationApiData = (
    apiCurrentCustomerUserData: CurrentCustomerUserQuery['currentCustomerUser'] | undefined,
): Partial<ContactInformation> | null => {
    if (!apiCurrentCustomerUserData) {
        return null;
    }

    return {
        ...apiCurrentCustomerUserData,
        companyName:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyName !== null
                ? apiCurrentCustomerUserData.companyName
                : '',
        companyNumber:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyNumber !== null
                ? apiCurrentCustomerUserData.companyNumber
                : '',
        companyTaxNumber:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyTaxNumber !== null
                ? apiCurrentCustomerUserData.companyTaxNumber
                : '',
        telephone: apiCurrentCustomerUserData.telephone !== null ? apiCurrentCustomerUserData.telephone : '',
        country: {
            value: apiCurrentCustomerUserData.country.code,
            label: apiCurrentCustomerUserData.country.name,
        },
        deliveryAddressUuid: apiCurrentCustomerUserData.defaultDeliveryAddress?.uuid ?? '',
        customer:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser'
                ? CustomerTypeEnum.CompanyCustomer
                : CustomerTypeEnum.CommonCustomer,
        note: '',
    };
};
