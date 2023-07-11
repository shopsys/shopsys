import { showErrorMessage } from 'components/Helpers/toasts';
import { removeTokensFromCookies } from 'helpers/auth/tokens';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { logException } from 'helpers/errors/logException';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { Exchange } from 'urql';
import { pipe, tap } from 'wonka';

export const getErrorExchange =
    (t?: Translate, context?: GetServerSidePropsContext | NextPageContext): Exchange =>
    ({ forward }) => {
        return (operations$) => {
            return pipe(
                operations$,
                forward,
                tap(({ error, operation }) => {
                    if (operation.kind !== 'query' || !error) {
                        return;
                    }

                    const isAuthError = error.response?.status === 401;
                    if (isAuthError) {
                        removeTokensFromCookies(context);
                    }

                    const parsedErrors = t ? getUserFriendlyErrors(error, t) : undefined;
                    logException(error);

                    if (parsedErrors?.applicationError) {
                        showErrorMessage(parsedErrors.applicationError.message);
                    }
                }),
            );
        };
    };
