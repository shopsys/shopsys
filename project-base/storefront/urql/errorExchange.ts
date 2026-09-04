import { DocumentNode, Kind, OperationDefinitionNode } from 'graphql';
import { CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { ParsedErrors } from 'types/error';
import { CombinedError, Exchange, Operation } from 'urql';
import { clearAuthCookies } from 'utils/auth/authMutationFetcher';
import { removeAccessTokenFromCookies, removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { FlashMessageKeys, isNoLogError } from 'utils/errors/applicationErrors';
import { ErrorOrchestrator } from 'utils/errors/ErrorOrchestrator';
import { getErrorMessage } from 'utils/errors/errorMessageMapper';
import { isExpectedPriceFilterError } from 'utils/errors/expectedErrors';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { isAuthError } from 'utils/errors/isAuthError';
import { isWithErrorDebugging, isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';
import { logException } from 'utils/errors/logException';
import { mapGraphqlErrorForDevelopment } from 'utils/errors/mapGraphqlErrorForDevelopment';
import { isClient } from 'utils/isClient';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { pipe, tap } from 'wonka';

type MutationErrorConfig = {
    errorType: FlashMessageKeys;
    gtmOrigin: GtmMessageOriginType;
    validationFields?: string[];
    suppressValidationErrors?: boolean;
};

const MUTATION_ERROR_CONFIG: Partial<Record<string, MutationErrorConfig>> = {
    AddToCartMutation: {
        errorType: 'add-to-cart-error',
        gtmOrigin: GtmMessageOriginType.product_detail_page,
    },
    AddOrderItemsToCartMutation: {
        errorType: 'add-order-items-error',
        gtmOrigin: GtmMessageOriginType.other,
    },
    ApplyPromoCodeToCartMutation: {
        errorType: 'promo-code-apply-error',
        gtmOrigin: GtmMessageOriginType.cart,
        validationFields: ['promoCode'],
    },
    RemovePromoCodeFromCartMutation: {
        errorType: 'promo-code-remove-error',
        gtmOrigin: GtmMessageOriginType.cart,
        validationFields: ['promoCode'],
    },
    RemoveFromCartMutation: {
        errorType: 'remove-from-cart-error',
        gtmOrigin: GtmMessageOriginType.cart,
    },
    SetCartItemAdditionalServicesMutation: {
        errorType: 'set-cart-item-additional-services-error',
        gtmOrigin: GtmMessageOriginType.cart,
    },
    ChangePaymentInCartMutation: {
        errorType: 'payment-error',
        gtmOrigin: GtmMessageOriginType.transport_and_payment_page,
        validationFields: ['payment', 'goPaySwift'],
    },
    ChangeTransportInCartMutation: {
        errorType: 'transport-error',
        gtmOrigin: GtmMessageOriginType.transport_and_payment_page,
        validationFields: ['transport', 'pickupPlaceIdentifier'],
    },
};

export const getErrorExchange =
    (t: Translate, domainConfig: DomainConfigType, context?: GetServerSidePropsContext | NextPageContext): Exchange =>
    ({ forward }) => {
        return (operations$) => {
            return pipe(
                operations$,
                forward,
                tap(({ error, operation }) => {
                    if ((operation.kind !== 'query' && operation.kind !== 'mutation') || !error) {
                        return;
                    }

                    const isMaintenance = error.response?.status === 503;
                    if (isMaintenance) {
                        return;
                    }

                    if (isAuthError(error)) {
                        if (context) {
                            removeTokensFromCookies(domainConfig, context);
                        } else {
                            removeAccessTokenFromCookies(domainConfig);
                            void clearAuthCookies(domainConfig).catch(logException);
                        }

                        return;
                    }

                    if (operation.kind === 'mutation') {
                        handleErrorMessagesForMutation(error, t, operation);

                        return;
                    }

                    if (isClient && hasFriendlyUrlQueryFailedWith500(operation, error)) {
                        if (isWithErrorDebugging) {
                            throw new Error(JSON.stringify(mapGraphqlErrorForDevelopment(error.graphQLErrors[0])));
                        }

                        throw new Error('Internal Server Error');
                    }

                    if (isWithErrorDebugging) {
                        handleErrorMessagesForDevelopment(error, t);
                    } else {
                        handleErrorMessagesForUsers(error, t, operation);
                    }
                }),
            );
        };
    };

const handleErrorMessagesForDevelopment = (error: CombinedError, t: Translate) => {
    const parsedErrors = getUserFriendlyErrors(error, t);

    if (parsedErrors.userError) {
        return;
    }

    if (!parsedErrors.applicationError || !isNoLogError(parsedErrors.applicationError.type)) {
        logException({
            message: error.message,
            originalError: JSON.stringify(error),
            location: 'getErrorExchange.handleErrorMessagesForDevelopment',
        });
    }

    if (isWithToastAndConsoleErrorDebugging) {
        error.graphQLErrors
            .map((graphqlError) => mapGraphqlErrorForDevelopment(graphqlError))
            .forEach((simplifiedGraphqlError) => showErrorMessage(JSON.stringify(simplifiedGraphqlError)));
    }
};

const getMutationName = (operation: Operation): string => {
    const query = operation.query as DocumentNode;
    const operationDefinition = query.definitions.find((definition) => definition.kind === 'OperationDefinition') as
        | OperationDefinitionNode
        | undefined;

    return operationDefinition?.name?.value ?? 'UnknownMutation';
};

const handleErrorMessagesForMutation = (error: CombinedError, t: Translate, operation: Operation) => {
    const parsedErrors = getUserFriendlyErrors(error, t);
    const mutationName = getMutationName(operation);
    const config = MUTATION_ERROR_CONFIG[mutationName];

    if (!isNoLogError(parsedErrors.applicationError?.type ?? '')) {
        logException({
            message: error.message,
            originalError: JSON.stringify(error),
            location: `getErrorExchange.handleErrorMessagesForMutation.${mutationName}`,
        });
    }

    if (isWithToastAndConsoleErrorDebugging) {
        // biome-ignore lint/suspicious/noConsole: intentional error logging
        console.error(
            '[Mutation Error]',
            mutationName,
            error.graphQLErrors.map((e) => mapGraphqlErrorForDevelopment(e)),
        );
    }

    // If no config for this mutation, skip toast (mutation handles its own errors or doesn't need toast)
    if (!config) {
        return;
    }

    const { userError } = parsedErrors;

    // Silently suppress validation errors (e.g. transport weight limit exceeded when restoring from last order)
    const suppressErrors = operation.context.suppressValidationErrors ?? config.suppressValidationErrors;
    if (userError?.validation && suppressErrors) {
        return;
    }

    // Handle validation errors for configured fields
    if (userError?.validation && config.validationFields) {
        for (const fieldName of config.validationFields) {
            const fieldError = userError.validation[fieldName];
            if (fieldError) {
                showErrorMessage(fieldError.message, config.gtmOrigin, {
                    errorType: config.errorType,
                    fieldName,
                });
            }
        }

        // If we showed validation errors, don't show generic error
        const hasValidationError = config.validationFields.some((field) => userError.validation?.[field]);
        if (hasValidationError) {
            return;
        }
    }

    // Show generic error message for this mutation type
    showErrorMessage(getErrorMessage(config.errorType, t), config.gtmOrigin, {
        errorType: config.errorType,
    });
};

const handleErrorMessagesForUsers = (error: CombinedError, t: Translate, operation: Operation) => {
    const parsedErrors = getUserFriendlyErrors(error, t);
    const isCartError = operation.query === CartQueryDocument;

    if (isCartError) {
        handleCartErrorMessages(parsedErrors);
        return;
    }

    if (isExpectedPriceFilterError(error)) {
        return;
    }

    // Skip network errors for queries - they shouldn't show individual toasts
    // (would cause toast spam when multiple queries fail at once)
    if (parsedErrors.networkError) {
        return;
    }

    // Skip if no application error to handle
    if (!parsedErrors.applicationError) {
        return;
    }

    // Use ErrorOrchestrator for consistent error handling
    const decisions = ErrorOrchestrator.decide(parsedErrors, {
        source: 'errorExchange',
        gtmOrigin: GtmMessageOriginType.other,
    });

    for (const decision of decisions) {
        switch (decision.action) {
            case 'toast':
                showErrorMessage(decision.message, GtmMessageOriginType.other, {
                    errorType: decision.errorType,
                });
                break;

            case 'silent-log':
                logException({
                    message: error.message,
                    parsedApplicationError: parsedErrors.applicationError,
                    originalError: JSON.stringify(error),
                    location: 'getErrorExchange.handleErrorMessagesForUsers',
                });
                break;

            case 'ignore':
            case 'form-field':
            case 'custom':
                // These actions are handled by component-level error handlers
                break;
        }
    }
};

const handleCartErrorMessages = ({ userError, applicationError }: ParsedErrors) => {
    switch (applicationError?.type) {
        case 'cart-not-found':
            break;
        case 'default':
            showErrorMessage(applicationError.message, GtmMessageOriginType.cart, {
                errorType: applicationError.type,
            });
            break;
    }

    if (userError?.validation) {
        for (const invalidFieldName in userError.validation) {
            const fieldError = userError.validation[invalidFieldName];
            if (fieldError) {
                showErrorMessage(fieldError.message, GtmMessageOriginType.cart, {
                    fieldName: invalidFieldName,
                });
            }
        }
    }
};

const hasFriendlyUrlQueryFailedWith500 = (operation: Operation, error: CombinedError) =>
    error.graphQLErrors.some(({ extensions }) => extensions.code === 500) &&
    (operation.query as DocumentNode).definitions.some(
        (definition) =>
            definition.kind === Kind.OPERATION_DEFINITION &&
            definition.directives?.some((directiveDefinition) => directiveDefinition.name.value === 'friendlyUrl'),
    );
