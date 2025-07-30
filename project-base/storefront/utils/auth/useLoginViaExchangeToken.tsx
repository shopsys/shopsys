import {
    TypeLoginViaExchangeTokenMutationVariables,
    TypeLoginViaExchangeTokenMutation,
    useLoginViaExchangeTokenMutation,
} from 'graphql/requests/auth/mutations/LoginViaExchangeTokenMutation.generated';
import { OperationResult } from 'urql';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';

type LoginViaExchangeTokenHandler = (
    exchangeToken: string,
) => Promise<OperationResult<TypeLoginViaExchangeTokenMutation, TypeLoginViaExchangeTokenMutationVariables>>;

export const useLoginViaExchangeToken = () => {
    const [, loginViaExchangeTokenMutation] = useLoginViaExchangeTokenMutation();
    const handleActionsAfterLogin = useHandleActionsAfterLogin();

    const loginViaExchangeToken: LoginViaExchangeTokenHandler = async (exchangeToken) => {
        const loginResult = await loginViaExchangeTokenMutation({ exchangeToken });

        if (loginResult.data) {
            const accessToken = loginResult.data.LoginViaExchangeToken.accessToken;
            const refreshToken = loginResult.data.LoginViaExchangeToken.refreshToken;

            setTokensToCookies(accessToken, refreshToken);

            // For login-as-user from admin, don't show cart merge info and clean URL
            handleActionsAfterLogin(false, '/');
        }

        return loginResult;
    };

    return loginViaExchangeToken;
};
