import { GtmUserEntryInfoType } from 'gtm/types/objects';
import { CurrentCustomerType } from 'types/customer';

export const mapGtmUserEntryInfoFromCurrentCustomer = ({
    uuid,
    email,
    firstName,
    lastName,
    loginInfo: { loginType, externalId },
}: CurrentCustomerType): GtmUserEntryInfoType => ({
    id: uuid,
    email,
    firstName,
    lastName,
    loginType,
    externalId,
});
