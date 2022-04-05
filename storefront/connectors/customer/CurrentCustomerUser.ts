import { CurrentCustomerUserQueryApi, useCurrentCustomerUserQueryApi } from 'graphql/generated';
import { ContactInformationFormType } from 'types/form';
import { CustomerTypeEnum } from 'types/customer';

export function useCurrentCustomerUser(): ContactInformationFormType | undefined {
    const [{ data }] = useCurrentCustomerUserQueryApi();

    if (data?.currentCustomerUser === undefined) {
        return undefined;
    }

    return mapCurrentCustomerUserApiData(data);
}

const mapCurrentCustomerUserApiData = (
    apiCurrentCustomerUserData: CurrentCustomerUserQueryApi,
): ContactInformationFormType | undefined => {
    const companyCustomerUser = apiCurrentCustomerUserData.currentCustomerUser;

    const mappedCurrentCustomerUserData = {
        ...companyCustomerUser,
        companyName:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.currentCustomerUser.companyName !== null
                ? apiCurrentCustomerUserData.currentCustomerUser.companyName
                : '',
        companyNumber:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.currentCustomerUser.companyNumber !== null
                ? apiCurrentCustomerUserData.currentCustomerUser.companyNumber
                : '',
        companyTaxNumber:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.currentCustomerUser.companyTaxNumber !== null
                ? apiCurrentCustomerUserData.currentCustomerUser.companyTaxNumber
                : '',
        telephone: companyCustomerUser.telephone !== null ? companyCustomerUser.telephone : '',
        country: {
            value: companyCustomerUser.country.code,
            label: companyCustomerUser.country.name,
        },
        deliveryFirstName:
            companyCustomerUser.defaultDeliveryAddress !== null
                ? companyCustomerUser.deliveryAddresses[0].firstName
                : '',
        deliveryLastName:
            companyCustomerUser.defaultDeliveryAddress !== null
                ? companyCustomerUser.deliveryAddresses[0].lastName
                : '',
        deliveryCompanyName:
            companyCustomerUser.defaultDeliveryAddress !== null
                ? companyCustomerUser.deliveryAddresses[0].companyName
                : '',
        deliveryTelephone:
            companyCustomerUser.defaultDeliveryAddress !== null
                ? companyCustomerUser.deliveryAddresses[0].telephone
                : '',
        deliveryStreet:
            companyCustomerUser.defaultDeliveryAddress !== null ? companyCustomerUser.deliveryAddresses[0].street : '',
        deliveryCity:
            companyCustomerUser.defaultDeliveryAddress !== null ? companyCustomerUser.deliveryAddresses[0].city : '',
        deliveryPostcode:
            companyCustomerUser.defaultDeliveryAddress !== null
                ? companyCustomerUser.deliveryAddresses[0].postcode
                : '',
        deliveryCountry: {
            value:
                companyCustomerUser.defaultDeliveryAddress !== null
                    ? companyCustomerUser.deliveryAddresses[0].country.code
                    : '',
            label:
                companyCustomerUser.defaultDeliveryAddress !== null
                    ? companyCustomerUser.deliveryAddresses[0].country.name
                    : '',
        },
        register: false,
        passwordFirst: '',
        passwordSecond: '',
        customer:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser'
                ? CustomerTypeEnum.CompanyCustomer
                : CustomerTypeEnum.CommonCustomer,
        differentDeliveryAddress: companyCustomerUser.defaultDeliveryAddress !== null,
    };

    return mappedCurrentCustomerUserData;
};
