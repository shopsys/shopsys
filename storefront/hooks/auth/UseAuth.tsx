import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { Exact, LoginApi, LogoutApi, useLoginApi, useLogoutApi } from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { UseMutationResponse } from 'urql';

export const useAuth = (): [
    UseMutationResponse<
        LoginApi,
        Exact<{
            email: string;
            password: any;
        }>
    >,
    UseMutationResponse<
        LogoutApi,
        Exact<{
            [key: string]: never;
        }>
    >,
    [boolean, Dispatch<SetStateAction<boolean>>],
] => {
    const loginUseMutationResponse = useLoginApi();
    const logoutUseMutationResponse = useLogoutApi();
    const [isUserLoggedIn, setLoggedInUser] = useState(false);

    useEffect(() => {
        if (
            loginUseMutationResponse[0].data?.Login.accessToken !== undefined &&
            loginUseMutationResponse[0].data?.Login.refreshToken !== undefined
        ) {
            setLoggedInUser(true);
            setTokensToCookie(
                loginUseMutationResponse[0].data.Login.accessToken,
                loginUseMutationResponse[0].data?.Login.refreshToken,
            );
        }
    }, [loginUseMutationResponse[0].data?.Login]);

    useEffect(() => {
        if (logoutUseMutationResponse[0].data?.Logout === true) {
            setLoggedInUser(false);
            removeTokensFromCookies();
        }
    }, [logoutUseMutationResponse[0].data]);

    return [loginUseMutationResponse, logoutUseMutationResponse, [isUserLoggedIn, setLoggedInUser]];
};
