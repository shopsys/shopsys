import { TypeLoginTypeEnum } from 'graphql/types';
import { GetServerSidePropsContext, NextPageContext } from 'next';

export type AuthContext = GetServerSidePropsContext | NextPageContext;

export type AuthNotification =
    | 'login'
    | 'login-with-cart-modifications'
    | 'logout'
    | 'registration'
    | 'registration-with-cart-modifications'
    | { type: 'social-login-fail'; socialNetworkType?: TypeLoginTypeEnum };
