import { CartQueryDocumentApi } from 'graphql/generated';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { removeTokensFromCookies } from 'helpers/auth/tokens';
import { isFlashMessageError, isNoLogError } from 'helpers/errors/applicationErrors';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { logException } from 'helpers/errors/logException';
import { showErrorMessage } from 'helpers/toasts';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ParsedErrors } from 'types/error';
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

                        return;
                    }

                    const parsedErrors = t ? getUserFriendlyErrors(error, t) : undefined;
                    if (!parsedErrors) {
                        return;
                    }

                    if (parsedErrors.userError) {
                        logException(parsedErrors.userError);
                    }

                    const isCartError = operation.query === CartQueryDocumentApi;
                    if (isCartError) {
                        handleCartError(parsedErrors);

                        return;
                    }

                    if (!parsedErrors.applicationError) {
                        return;
                    }

                    if (!isNoLogError(parsedErrors.applicationError.type)) {
                        logException(parsedErrors.applicationError);
                    }

                    if (isFlashMessageError(parsedErrors.applicationError.type)) {
                        showErrorMessage(parsedErrors.applicationError.message);
                    }
                }),
            );
        };
    };

const handleCartError = ({ userError, applicationError }: ParsedErrors) => {
    switch (applicationError?.type) {
        case 'cart-not-found':
            break;
        case 'default':
            showErrorMessage(applicationError.message, GtmMessageOriginType.cart);
            break;
    }

    if (userError?.validation) {
        for (const invalidFieldName in userError.validation) {
            showErrorMessage(userError.validation[invalidFieldName].message, GtmMessageOriginType.cart);
        }
    }
};
