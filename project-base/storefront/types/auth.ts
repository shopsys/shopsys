import { TypeLoginTypeEnum } from 'graphql/types';

export type AuthNotification =
    | 'login'
    | 'login-with-cart-modifications'
    | 'logout'
    | 'registration'
    | 'registration-with-cart-modifications'
    | { type: 'social-login-fail'; socialNetworkType?: TypeLoginTypeEnum };
