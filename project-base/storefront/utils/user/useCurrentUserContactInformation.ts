import {
    TypeCurrentCustomerUserQuery,
    useCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { CustomerTypeEnum } from 'types/customer';
import { SelectOptionType } from 'types/selectOptions';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';

export const useCurrentUserContactInformation = (): ContactInformation => {
    const [{ data: currentCustomerUserData }] = useCurrentCustomerUserQuery();
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    const contactInformationApiData = mapCurrentCustomerContactInformationApiData(
        currentCustomerUserData?.currentCustomerUser,
    );
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
    const filteredContactInformationFromApi: Partial<ContactInformation> = {
        ...contactInformationFromApi,
    };

    for (const key in filteredContactInformationFromApi) {
        const filteredProperty = filteredContactInformationFromApi[key as keyof ContactInformation];

        const isUndefined = filteredProperty === undefined;
        const isEmptyString = typeof filteredProperty === 'string' && filteredProperty.length === 0;

        if ((isUndefined || isEmptyString) && key in contactInformationFromApi) {
            delete filteredContactInformationFromApi[key as keyof ContactInformation];
        }
    }

    const contactInformation = {
        ...contactInformationFromStore,
        ...filteredContactInformationFromApi,
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
    apiCurrentCustomerUserData: TypeCurrentCustomerUserQuery['currentCustomerUser'] | undefined,
): Partial<ContactInformation> | null => {
    if (!apiCurrentCustomerUserData) {
        return null;
    }

    return {
        ...apiCurrentCustomerUserData,
        firstName: apiCurrentCustomerUserData.firstName ?? '',
        lastName: apiCurrentCustomerUserData.lastName ?? '',
        street: apiCurrentCustomerUserData.street ?? '',
        city: apiCurrentCustomerUserData.city ?? '',
        postcode: apiCurrentCustomerUserData.postcode ?? '',
        companyName:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' && apiCurrentCustomerUserData.companyName
                ? apiCurrentCustomerUserData.companyName
                : '',
        companyNumber:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' && apiCurrentCustomerUserData.companyNumber
                ? apiCurrentCustomerUserData.companyNumber
                : '',
        companyTaxNumber:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyTaxNumber
                ? apiCurrentCustomerUserData.companyTaxNumber
                : '',
        telephone: apiCurrentCustomerUserData.telephone ?? '',
        country: {
            value: apiCurrentCustomerUserData.country?.code ?? '',
            label: apiCurrentCustomerUserData.country?.name ?? '',
        },
        deliveryAddressUuid: apiCurrentCustomerUserData.defaultDeliveryAddress?.uuid ?? '',
        customer:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser'
                ? CustomerTypeEnum.CompanyCustomer
                : CustomerTypeEnum.CommonCustomer,
        note: '',
    };
};
