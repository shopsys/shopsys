import { NextRequest, NextResponse } from 'next/server';

export const validateAuthTokens = async (request: NextRequest) => {
    const response = NextResponse.next();

    const accessToken = request.cookies.get('accessToken')?.value;
    if (!accessToken) {
        return response;
    }

    try {
        // TODO: possibly replace this CurrentCustomerUserQuery with the `isAuthorized` query
        const currentUserResp = await gqlQueryFetch(getIsAuthenticatedBody(), accessToken);

        if (currentUserResp.status !== 401) {
            return response;
        }

        const refreshToken = request.cookies.get('refreshToken')?.value;
        if (!refreshToken) {
            deleteAuthTokensFromCookies(response);
            return response;
        }

        await refreshAuthTokensInCookies(response, refreshToken);
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Auth token validation error:', e);
    }

    return response;
};

async function refreshAuthTokensInCookies(response: NextResponse, refreshToken: string) {
    const refreshTokensReponse = await gqlQueryFetch(getRefreshTokensBody({ refreshToken }));

    if (!refreshTokensReponse.ok) {
        deleteAuthTokensFromCookies(response);
        return;
    }

    const { data } = await refreshTokensReponse.json();
    if (!data?.RefreshTokens) {
        deleteAuthTokensFromCookies(response);
        return;
    }

    const { accessToken: newAccessToken, refreshToken: newRrefreshToken } = data.RefreshTokens;
    response.cookies.set('accessToken', newAccessToken);
    response.cookies.set('refreshToken', newRrefreshToken);
    // eslint-disable-next-line no-console
    console.log('Tokens refreshed');
}

function gqlQueryFetch(body: any, accessToken?: string) {
    const defaultHeaders = {
        Accept: 'application/graphql-response+json, application/graphql+json, application/json, text/event-stream, multipart/mixed',
        Originalhost: '127.0.0.1:8000',
        'X-Forwarded-Proto': 'off',
        'Content-Type': 'application/json',
    };

    return fetch(`${process.env.INTERNAL_ENDPOINT}graphql/`, {
        headers: {
            ...defaultHeaders,
            ...(accessToken && { 'X-Auth-Token': `Bearer ${accessToken}` }),
        },
        body: JSON.stringify(body),
        method: 'POST',
        cache: 'no-store',
    });
}

function getIsAuthenticatedBody() {
    return {
        operationName: 'OrdersQuery',
        query: 'query OrdersQuery($after: String, $first: Int) { orders(after: $after, first: $first) { totalCount } }',
        variables: { after: '', first: 28 },
    };
}

function getRefreshTokensBody(variables = {}) {
    return {
        operationName: 'RefreshTokens',
        query: 'mutation RefreshTokens($refreshToken: String!) { RefreshTokens(input: {refreshToken: $refreshToken}) { ...TokenFragments } } fragment TokenFragments on Token { accessToken refreshToken }',
        variables,
    };
}

function deleteAuthTokensFromCookies(response: NextResponse) {
    response.cookies.delete('accessToken');
    response.cookies.delete('refreshToken');
}
