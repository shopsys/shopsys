import { showErrorMessage } from 'components/Helpers/toasts';
import { CartQueryDocumentApi } from 'graphql/generated';
import { removeTokensFromCookies } from 'helpers/auth/tokens';
import { ApplicationErrors } from 'helpers/errors/applicationErrors';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { logException } from 'helpers/errors/logException';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ParsedErrors } from 'types/error';
import { GtmMessageOriginType } from 'types/gtm/enums';
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

                    if (!parsedErrors) {
                        return;
                    }

                    const isCartError = operation.query === CartQueryDocumentApi;
                    if (isCartError) {
                        handleCartError(parsedErrors);

                        return;
                    }

                    if (parsedErrors.applicationError) {
                        showErrorMessage(parsedErrors.applicationError.message);
                    }
                }),
            );
        };
    };

const handleCartError = ({ userError, applicationError }: ParsedErrors) => {
    switch (applicationError?.type) {
        case ApplicationErrors['cart-not-found']:
            break;
        case ApplicationErrors.default:
            showErrorMessage(applicationError.message, GtmMessageOriginType.cart);
            break;
    }

    if (userError?.validation) {
        for (const invalidFieldName in userError.validation) {
            showErrorMessage(userError.validation[invalidFieldName].message, GtmMessageOriginType.cart);
        }
    }
};
