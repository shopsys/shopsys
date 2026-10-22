import { AuthConfig, AuthUtilities } from '@urql/exchange-auth';
import { AuthContext } from 'types/auth';
import { Operation } from 'urql';
import { addAuthToOperation, willAuthError } from 'utils/auth/authOperation';
import { refreshAuth } from 'utils/auth/refreshAuth';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { isAuthError } from 'utils/errors/isAuthError';

export const getAuthExchangeOptions =
    (domainConfig: DomainConfigType, context?: AuthContext) =>
    async (authUtilities: AuthUtilities): Promise<AuthConfig> => {
        let rejectedOperation: Operation | undefined;

        return {
            addAuthToOperation: (operation) => addAuthToOperation(operation, domainConfig, context),
            didAuthError: (error, operation) => {
                if (!isAuthError(error)) {
                    return false;
                }

                rejectedOperation = operation;

                return true;
            },
            willAuthError: () => willAuthError(domainConfig, context),
            refreshAuth: () => {
                const operation = rejectedOperation;
                rejectedOperation = undefined;

                return refreshAuth(authUtilities, domainConfig, context, operation);
            },
        };
    };
