import { showInfoMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import {
    LoginApi,
    LoginVariablesApi,
    LogoutApi,
    LogoutVariablesApi,
    useLoginApi,
    useLogoutApi,
} from 'graphql/generated';
import { canUseDom } from 'helpers/canUseDom';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useShopsysDispatch } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { UseMutationState } from 'urql';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';

export const useAuth = (): [
    [UseMutationState<LoginApi, LoginVariablesApi>, typeof loginHandler],
    [UseMutationState<LogoutApi, LogoutVariablesApi>, typeof logoutHandler],
] => {
    const [loginUseMutationResponse, login] = useLoginApi();
    const [logoutUseMutationResponse, logout] = useLogoutApi();
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();

    const router = useRouter();

    const loginHandler = async (variables: LoginVariablesApi, rewriteUrl?: string) => {
        const loginResult = await login(variables);

        if (loginResult.error !== undefined) {
            return;
        }

        const accessToken = loginResult.data?.Login.tokens.accessToken;
        const refreshToken = loginResult.data?.Login.tokens.refreshToken;

        if (accessToken !== undefined && refreshToken !== undefined) {
            dispatch(userActions.setCartUuid(null));
            setTokensToCookie(accessToken, refreshToken);
            showSuccessMessage(t('Successfully logged in'));

            if (loginResult.data?.Login.showCartMergeInfo === true) {
                showInfoMessage(t('Your cart has been modified. Please check the changes.'));
            }

            if (canUseDom()) {
                window.location.href = rewriteUrl ?? router.asPath;
            }
        }
    };

    const logoutHandler = async () => {
        const logoutResult = await logout();

        if (logoutResult.data?.Logout === true) {
            removeTokensFromCookies();
            showSuccessMessage(t('Successfully logged out'));

            if (canUseDom()) {
                router.reload();
            }
        }
    };

    return [
        [loginUseMutationResponse, loginHandler],
        [logoutUseMutationResponse, logoutHandler],
    ];
};
