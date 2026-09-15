import { TypeLoginTypeEnum } from 'graphql/types';

export type SocialNetworkLoginType = TypeLoginTypeEnum.Facebook | TypeLoginTypeEnum.Google | TypeLoginTypeEnum.Seznam;

export const getAllowedSocialNetworkType = (
    socialNetworkType: string | undefined,
): SocialNetworkLoginType | undefined => {
    switch (socialNetworkType) {
        case TypeLoginTypeEnum.Facebook:
            return TypeLoginTypeEnum.Facebook;
        case TypeLoginTypeEnum.Google:
            return TypeLoginTypeEnum.Google;
        case TypeLoginTypeEnum.Seznam:
            return TypeLoginTypeEnum.Seznam;
        default:
            return undefined;
    }
};
