import { GtmUserEntryInfoType, GtmUserInfoType } from 'gtm/types/objects';

export const mapGtmUserEntryInfoFromGtmUserInfo = ({
    id,
    email,
    firstName,
    lastName,
    loginType,
    externalId,
}: GtmUserInfoType): GtmUserEntryInfoType => ({
    id,
    email,
    firstName,
    lastName,
    loginType,
    externalId,
});
