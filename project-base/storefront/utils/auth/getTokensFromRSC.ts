'use server';

import { getCookie } from 'cookies-next';
import { cookies } from 'next/headers';
import { OptionalTokenType } from 'urql/types';

export const getTokensRSC = async (): Promise<OptionalTokenType> => {
    let accessToken = await getCookie('accessToken', {
        cookies,
    });
    let refreshToken = await getCookie('refreshToken', {
        cookies,
    });

    if (typeof accessToken !== 'string' || accessToken.length === 0) {
        accessToken = undefined;
    }

    if (typeof refreshToken !== 'string' || refreshToken.length === 0) {
        refreshToken = undefined;
    }

    return { accessToken, refreshToken };
};
