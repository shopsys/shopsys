import {
    LoginApi,
    LoginVariablesApi,
    LogoutApi,
    LogoutVariablesApi,
    useLoginApi,
    useLogoutApi,
} from 'graphql/generated';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { UseMutationState } from 'urql';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useShopsysDispatch } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useAuth = (): [
    [UseMutationState<LoginApi, LoginVariablesApi>, (variables: LoginVariablesApi) => void],
    [UseMutationState<LogoutApi, LogoutVariablesApi>, () => void],
] => {
    const [loginUseMutationResponse, login] = useLoginApi();
    const [logoutUseMutationResponse, logout] = useLogoutApi();
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const router = useRouter();

    const loginHandler = async (variables: LoginVariablesApi) => {
        const loginResult = await login(variables);

        if (loginUseMutationResponse.error !== undefined) {
            showErrorMessage(t('You have entered an incorrect email or password.'));
            return;
        }

        const accessToken = loginResult.data?.Login.accessToken;
        const refreshToken = loginResult.data?.Login.refreshToken;

        if (accessToken !== undefined && refreshToken !== undefined) {
            dispatch(userActions.setCartUuid(null));
            setTokensToCookie(accessToken, refreshToken);
            showSuccessMessage(t('Successfully logged in'));
            window.location.href = router.asPath;
        }
    };

    const logoutHandler = async () => {
        const logoutResult = await logout();

        if (logoutResult.data?.Logout === true) {
            removeTokensFromCookies();
            showSuccessMessage(t('Successfully logged out'));
            window.location.href = router.asPath;
        }
    };

    return [
        [loginUseMutationResponse, loginHandler],
        [logoutUseMutationResponse, logoutHandler],
    ];
};
