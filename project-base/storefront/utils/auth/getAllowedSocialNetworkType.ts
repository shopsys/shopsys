import { TypeLoginTypeEnum } from 'graphql/types';

export const getAllowedSocialNetworkType = (socialNetworkType: string | undefined): TypeLoginTypeEnum | undefined => {
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
