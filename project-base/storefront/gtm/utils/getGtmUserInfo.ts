import { SHA256 } from 'crypto-js';
import { GtmUserStatus } from 'gtm/enums/GtmUserStatus';
import { GtmUserType } from 'gtm/enums/GtmUserType';
import { GtmUserInfoType } from 'gtm/types/objects';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType, CustomerTypeEnum } from 'types/customer';

export const getGtmUserInfo = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
): GtmUserInfoType => {
    const userInfo: GtmUserInfoType = getGtmUserInfoForVisitor(userContactInformation);

    if (currentSignedInCustomer) {
        overwriteGtmUserInfoWithLoggedCustomer(userInfo, currentSignedInCustomer, userContactInformation);
    }

    return userInfo;
};

const getGtmUserInfoForVisitor = (userContactInformation: ContactInformation) => ({
    status: GtmUserStatus.visitor,
    ...(userContactInformation.city.length > 0 && { city: userContactInformation.city }),
    ...(userContactInformation.country.value.length > 0 && { country: userContactInformation.country.value }),
    ...(userContactInformation.email.length > 0 && { email: userContactInformation.email }),
    ...(userContactInformation.email.length > 0 && { emailHash: SHA256(userContactInformation.email).toString() }),
    ...(userContactInformation.firstName.length > 0 && { firstName: userContactInformation.firstName }),
    ...(userContactInformation.telephone.length > 0 && { telephone: userContactInformation.telephone }),
    ...(userContactInformation.postcode.length > 0 && { postcode: userContactInformation.postcode }),
    ...(userContactInformation.street.length > 0 && { street: userContactInformation.street }),
    ...(userContactInformation.lastName.length > 0 && { lastName: userContactInformation.lastName }),
    type: getGtmUserType(userContactInformation.customer),
});

const getGtmUserType = (customerType: CustomerTypeEnum | undefined): GtmUserType | undefined => {
    if (customerType === undefined) {
        return undefined;
    }

    if (customerType === CustomerTypeEnum.CompanyCustomer) {
        return GtmUserType.b2b;
    }

    return GtmUserType.b2c;
};

const overwriteGtmUserInfoWithLoggedCustomer = (
    userInfo: GtmUserInfoType,
    currentSignedInCustomer: CurrentCustomerType,
    userContactInformation: ContactInformation,
) => {
    userInfo.status = GtmUserStatus.customer;
    userInfo.id = currentSignedInCustomer.uuid;
    userInfo.group = currentSignedInCustomer.pricingGroup;

    if (userInfo.street === undefined || userInfo.street.length === 0) {
        userInfo.street = currentSignedInCustomer.street;
    }
    if (userInfo.city === undefined || userInfo.city.length === 0) {
        userInfo.city = currentSignedInCustomer.city;
    }
    if (userInfo.postcode === undefined || userInfo.postcode.length === 0) {
        userInfo.postcode = currentSignedInCustomer.postcode;
    }
    if (userInfo.country === undefined || userInfo.country.length === 0) {
        userInfo.country = currentSignedInCustomer.country.code;
    }
    if (userInfo.email === undefined || userInfo.email.length === 0) {
        userInfo.email = userContactInformation.email || currentSignedInCustomer.email;
    }
    if (userInfo.emailHash === undefined || userInfo.emailHash.length === 0) {
        userInfo.emailHash = SHA256(currentSignedInCustomer.email).toString();
    }
    if (userInfo.telephone === undefined || userInfo.telephone.length === 0) {
        userInfo.telephone = currentSignedInCustomer.telephone;
    }
    if (userInfo.firstName === undefined || userInfo.firstName.length === 0) {
        userInfo.firstName = currentSignedInCustomer.firstName;
    }
    if (userInfo.lastName === undefined || userInfo.lastName.length === 0) {
        userInfo.lastName = currentSignedInCustomer.lastName;
    }

    if (userInfo.type !== undefined) {
        return;
    }

    if (currentSignedInCustomer.companyCustomer) {
        userInfo.type = GtmUserType.b2b;
    } else {
        userInfo.type = GtmUserType.b2c;
    }
};
