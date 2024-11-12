'use server';

import { getCookie } from 'cookies-next';
import { cookies, headers } from 'next/headers';
import { OptionalTokenType } from 'urql/types';
import { getIsHttps, getProtocolFromServer } from 'utils/requestProtocol';

export const getTokensFromCookiesServer = async (): Promise<OptionalTokenType> => {
    let accessToken = await getCookie('accessToken', {
        cookies,
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });
    let refreshToken = await getCookie('refreshToken', {
        cookies,
        secure: getIsHttps(getProtocolFromServer(headers().get('host')!)),
    });

    if (typeof accessToken !== 'string' || accessToken.length === 0) {
        accessToken = undefined;
    }

    if (typeof refreshToken !== 'string' || refreshToken.length === 0) {
        refreshToken = undefined;
    }

    return { accessToken, refreshToken };
};
