import { TypeCreateOrderMutationVariables } from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type DeliveryInfo = Pick<
    TypeCreateOrderMutationVariables,
    | 'deliveryAddressUuid'
    | 'deliveryCity'
    | 'deliveryCompanyName'
    | 'deliveryFirstName'
    | 'deliveryLastName'
    | 'deliveryPostcode'
    | 'deliveryStreet'
    | 'deliveryTelephone'
    | 'isDeliveryAddressDifferentFromBilling'
>;

export const getEmptyDeliveryInfo = (): DeliveryInfo => ({
    deliveryFirstName: null,
    deliveryLastName: null,
    deliveryCompanyName: null,
    deliveryTelephone: null,
    deliveryStreet: null,
    deliveryCity: null,
    deliveryPostcode: null,
    isDeliveryAddressDifferentFromBilling: false,
    deliveryAddressUuid: null,
});

export const getDeliveryInfoFromFormValues = (formValues: ContactInformation): DeliveryInfo => ({
    deliveryFirstName: formValues.deliveryFirstName,
    deliveryLastName: formValues.deliveryLastName,
    deliveryCompanyName: formValues.deliveryCompanyName,
    deliveryTelephone: formValues.deliveryTelephone,
    deliveryStreet: formValues.deliveryStreet,
    deliveryCity: formValues.deliveryCity,
    deliveryPostcode: formValues.deliveryPostcode,
    isDeliveryAddressDifferentFromBilling: true,
    deliveryAddressUuid: null,
});

export const getSelectedDeliveryAddressForLoggedInUser = (
    user: CurrentCustomerType | null | undefined,
    formValues: ContactInformation,
) => user?.deliveryAddresses.find((address) => address.uuid === formValues.deliveryAddressUuid);

export const getDeliveryInfoFromSelectedPickupPlace = (
    formValues: ContactInformation,
    selectedPickupPlace: StoreOrPacketeryPoint,
): DeliveryInfo => ({
    deliveryFirstName: formValues.isDeliveryAddressDifferentFromBilling
        ? formValues.deliveryFirstName
        : formValues.firstName,
    deliveryLastName: formValues.isDeliveryAddressDifferentFromBilling
        ? formValues.deliveryLastName
        : formValues.lastName,
    deliveryTelephone: formValues.isDeliveryAddressDifferentFromBilling
        ? formValues.deliveryTelephone
        : formValues.telephone,
    deliveryStreet: selectedPickupPlace.street,
    deliveryCity: selectedPickupPlace.city,
    deliveryPostcode: selectedPickupPlace.postcode,
    deliveryCompanyName: null,
    isDeliveryAddressDifferentFromBilling: true,
    deliveryAddressUuid: null,
});

export const getDeliveryInfoFromSavedAndSelectedDeliveryAddress = (
    savedAndSelectedDeliveryAddressUuid: string,
): DeliveryInfo => ({
    deliveryFirstName: null,
    deliveryLastName: null,
    deliveryCompanyName: null,
    deliveryTelephone: null,
    deliveryStreet: null,
    deliveryCity: null,
    deliveryPostcode: null,
    isDeliveryAddressDifferentFromBilling: true,
    deliveryAddressUuid: savedAndSelectedDeliveryAddressUuid,
});

export const getFormValuesWithoutDeliveryInfo = (
    formValues: ContactInformation,
): Omit<ContactInformation, keyof DeliveryInfo | 'deliveryCountry'> => ({
    city: formValues.city,
    companyName: formValues.companyName,
    companyNumber: formValues.companyNumber,
    companyTaxNumber: formValues.companyTaxNumber,
    country: formValues.country,
    customer: formValues.customer,
    email: formValues.email,
    firstName: formValues.firstName,
    isWithoutHeurekaAgreement: formValues.isWithoutHeurekaAgreement,
    lastName: formValues.lastName,
    newsletterSubscription: formValues.newsletterSubscription,
    note: formValues.note,
    postcode: formValues.postcode,
    street: formValues.street,
    telephone: formValues.telephone,
});
