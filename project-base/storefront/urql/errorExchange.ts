import { Kind } from 'graphql';
import { CartQueryDocumentApi } from 'graphql/generated';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { removeTokensFromCookies } from 'helpers/auth/tokens';
import { isFlashMessageError, isNoLogError } from 'helpers/errors/applicationErrors';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { isWithErrorDebugging } from 'helpers/errors/isWithErrorDebugging';
import { logException } from 'helpers/errors/logException';
import { mapGraphqlErrorForDevelopment } from 'helpers/errors/mapGraphqlErrorForDevelopment';
import { isClient } from 'helpers/isClient';
import { showErrorMessage } from 'helpers/toasts';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ParsedErrors } from 'types/error';
import { CombinedError, Exchange, Operation } from 'urql';
import { pipe, tap } from 'wonka';

export const getErrorExchange =
    (t: Translate, context?: GetServerSidePropsContext | NextPageContext): Exchange =>
    ({ forward }) => {
        return (operations$) => {
            return pipe(
                operations$,
                forward,
                tap(({ error, operation }) => {
                    if ((operation.kind !== 'query' && operation.kind !== 'mutation') || !error) {
                        return;
                    }

                    if (isWithErrorDebugging && operation.kind === 'mutation') {
                        handleErrorMessagesForMutation(error);

                        return;
                    }

                    if (isClient && hasFriendlyUrlQueryFailedWith500(operation, error)) {
                        if (isWithErrorDebugging) {
                            throw new Error(JSON.stringify(mapGraphqlErrorForDevelopment(error.graphQLErrors[0])));
                        }

                        throw new Error('Internal Server Error');
                    }

                    const isAuthError = error.response?.status === 401;
                    if (isAuthError) {
                        removeTokensFromCookies(context);

                        return;
                    }

                    if (isWithErrorDebugging) {
                        handleErrorMessagesForDevelopment(error);
                    } else {
                        handleErrorMessagesForUsers(error, t, operation);
                    }
                }),
            );
        };
    };

const handleErrorMessagesForDevelopment = (error: CombinedError) => {
    logException({
        message: error.message,
        originalError: JSON.stringify(error),
        location: 'getErrorExchange.handleErrorMessagesForDevelopment',
    });
    error.graphQLErrors
        .map((graphqlError) => mapGraphqlErrorForDevelopment(graphqlError))
        .forEach((simplifiedGraphqlError) => showErrorMessage(JSON.stringify(simplifiedGraphqlError)));
};

const handleErrorMessagesForMutation = (error: CombinedError) => {
    if (isWithErrorDebugging) {
        logException({
            message: error.message,
            originalError: JSON.stringify(error),
            location: 'getErrorExchange.handleErrorMessagesForMutation',
        });

        error.graphQLErrors
            .map((graphqlError) => mapGraphqlErrorForDevelopment(graphqlError))
            .forEach((simplifiedGraphqlError) => showErrorMessage(JSON.stringify(simplifiedGraphqlError)));
    }
};

const handleErrorMessagesForUsers = (error: CombinedError, t: Translate, operation: Operation) => {
    const parsedErrors = getUserFriendlyErrors(error, t);
    const isCartError = operation.query === CartQueryDocumentApi;

    if (parsedErrors.userError) {
        logException({
            message: error.message,
            parsedUserError: parsedErrors.userError,
            originalError: JSON.stringify(error),
            location: 'getErrorExchange.handleErrorMessagesForUsers',
        });
    }

    if (isCartError) {
        handleCartErrorMessages(parsedErrors);

        return;
    }

    if (!parsedErrors.applicationError) {
        return;
    }

    if (!isNoLogError(parsedErrors.applicationError.type)) {
        logException({
            message: error.message,
            parsedApplicationError: parsedErrors.applicationError,
            originalError: JSON.stringify(error),
            location: 'getErrorExchange.handleErrorMessagesForUsers',
        });
    }

    if (isFlashMessageError(parsedErrors.applicationError.type)) {
        showErrorMessage(parsedErrors.applicationError.message);
    }
};

const handleCartErrorMessages = ({ userError, applicationError }: ParsedErrors) => {
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

const hasFriendlyUrlQueryFailedWith500 = (operation: Operation, error: CombinedError) =>
    error.graphQLErrors.some(({ extensions }) => extensions.code === 500) &&
    operation.query.definitions.some(
        (definition) =>
            definition.kind === Kind.OPERATION_DEFINITION &&
            definition.directives?.some((directiveDefinition) => directiveDefinition.name.value === 'friendlyUrl'),
    );
