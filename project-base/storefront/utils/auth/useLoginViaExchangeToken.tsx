import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    TypeLoginViaExchangeTokenMutation,
    TypeLoginViaExchangeTokenMutationVariables,
    useLoginViaExchangeTokenMutation,
} from 'graphql/requests/auth/mutations/LoginViaExchangeTokenMutation.generated';
import { OperationResult } from 'urql';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';

type LoginViaExchangeTokenHandler = (
    exchangeToken: string,
) => Promise<OperationResult<TypeLoginViaExchangeTokenMutation, TypeLoginViaExchangeTokenMutationVariables>>;

export const useLoginViaExchangeToken = () => {
    const [, loginViaExchangeTokenMutation] = useLoginViaExchangeTokenMutation();
    const handleActionsAfterLogin = useHandleActionsAfterLogin();
    const domainConfig = useDomainConfig();

    const loginViaExchangeToken: LoginViaExchangeTokenHandler = async (exchangeToken) => {
        const loginResult = await loginViaExchangeTokenMutation(
            { exchangeToken },
            { fetch: getAuthMutationFetcher(domainConfig) },
        );

        if (loginResult.data) {
            // For login-as-user from admin, don't show cart merge info and clean URL
            handleActionsAfterLogin(false, '/');
        }

        return loginResult;
    };

    return loginViaExchangeToken;
};
