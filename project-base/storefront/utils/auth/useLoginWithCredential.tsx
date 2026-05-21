import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    TypeLoginWithCredentialMutation,
    TypeLoginWithCredentialMutationVariables,
    useLoginWithCredentialMutation,
} from 'graphql/requests/auth/mutations/LoginWithCredentialMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { OperationResult } from 'urql';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';

type LoginWithCredentialHandler = (
    variables: Omit<TypeLoginWithCredentialMutationVariables, 'productListsUuids'>,
) => Promise<OperationResult<TypeLoginWithCredentialMutation, TypeLoginWithCredentialMutationVariables>>;

export const useLoginWithCredential = () => {
    const [, loginWithCredentialMutation] = useLoginWithCredentialMutation();
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const handleActionsAfterLogin = useHandleActionsAfterLogin();
    const domainConfig = useDomainConfig();

    const loginWithCredential: LoginWithCredentialHandler = async (variables) => {
        const loginResult = await loginWithCredentialMutation({
            ...variables,
            productListsUuids: Object.values(productListUuids),
        });

        if (loginResult.data) {
            const { accessToken, refreshToken } = loginResult.data.LoginWithCredential.tokens;

            setTokensToCookies(accessToken, refreshToken, domainConfig);

            handleActionsAfterLogin(loginResult.data.LoginWithCredential.showCartMergeInfo, undefined);
        }

        return loginResult;
    };

    return loginWithCredential;
};
