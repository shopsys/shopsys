import { useCurrentCustomerUserQueryApi, DeliveryAddressFragmentApi } from 'graphql/generated';
import { CurrentCustomerType, DeliveryAddressType } from 'types/customer';

export function useCurrentCustomerData(): CurrentCustomerType | null | undefined {
    const [{ data }] = useCurrentCustomerUserQueryApi();

    if (!data?.currentCustomerUser) {
        return undefined;
    }

    const { currentCustomerUser } = data;
    const isCompanyCustomer = currentCustomerUser.__typename === 'CompanyCustomerUser';

    return {
        ...currentCustomerUser,
        companyCustomer: isCompanyCustomer,
        telephone: currentCustomerUser.telephone ? currentCustomerUser.telephone : '',
        companyName: isCompanyCustomer && currentCustomerUser.companyName ? currentCustomerUser.companyName : '',
        companyNumber: isCompanyCustomer && currentCustomerUser.companyNumber ? currentCustomerUser.companyNumber : '',
        companyTaxNumber:
            isCompanyCustomer && currentCustomerUser.companyTaxNumber ? currentCustomerUser.companyTaxNumber : '',
        defaultDeliveryAddress: currentCustomerUser.defaultDeliveryAddress
            ? mapDeliveryAddress(currentCustomerUser.defaultDeliveryAddress)
            : undefined,
        deliveryAddresses: mapDeliveryAddresses(currentCustomerUser.deliveryAddresses),
        passwordOld: '',
        passwordFirst: '',
        passwordSecond: '',
    };
}

export const mapDeliveryAddress = (apiDeliveryAddressData: DeliveryAddressFragmentApi): DeliveryAddressType => {
    return {
        ...apiDeliveryAddressData,
        companyName: apiDeliveryAddressData.companyName ?? '',
        street: apiDeliveryAddressData.street ?? '',
        city: apiDeliveryAddressData.city ?? '',
        postcode: apiDeliveryAddressData.postcode ?? '',
        telephone: apiDeliveryAddressData.telephone ?? '',
        firstName: apiDeliveryAddressData.firstName ?? '',
        lastName: apiDeliveryAddressData.lastName ?? '',
        country: apiDeliveryAddressData.country?.name ?? '',
    };
};

export const mapDeliveryAddresses = (apiDeliveryAddressesData: DeliveryAddressFragmentApi[]): DeliveryAddressType[] => {
    return apiDeliveryAddressesData.map((address) => mapDeliveryAddress(address));
};
