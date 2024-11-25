import { captureException } from '@sentry/nextjs';
import { Translate as NextTranslate } from 'next-translate';
import { Translate } from 'types/translation';
import { AnyVariables, CombinedError, Operation } from 'urql';
import { isNoLogError } from 'utils/errors/applicationErrors';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { mapGraphqlErrorForDevelopment } from 'utils/errors/mapGraphqlErrorForDevelopment';
import { getServerT } from 'utils/getServerTranslation';
import { isEnvironment } from 'utils/isEnvironment';

const isWithConsoleErrorDebugging = process.env.ERROR_DEBUGGING_LEVEL === 'console';

const isWithToastAndConsoleErrorDebugging = process.env.ERROR_DEBUGGING_LEVEL === 'toast-and-console';

const isWithErrorDebugging = isWithConsoleErrorDebugging || isWithToastAndConsoleErrorDebugging;

export const getErrorExchange = async <Data, Variables extends AnyVariables>(
    error: CombinedError | undefined,
    operation: Operation<Data, Variables>,
) => {
    if ((operation.kind !== 'query' && operation.kind !== 'mutation') || !error) {
        return;
    }

    if (isWithErrorDebugging) {
        handleErrorMessagesForDevelopment(error);
    } else {
        const t = await getServerT();
        handleErrorMessagesForUsers(error, t);
    }
    handleErrorMessagesForDevelopment(error);
};

const handleErrorMessagesForDevelopment = (error: CombinedError) => {
    logException({
        message: error.message,
        originalError: JSON.stringify(error),
        location: 'getErrorExchange.handleErrorMessagesForDevelopment',
    });

    if (isWithToastAndConsoleErrorDebugging) {
        error.graphQLErrors
            .map((graphqlError) => mapGraphqlErrorForDevelopment(graphqlError))
            .forEach((simplifiedGraphqlError) => {
                throw new Error(JSON.stringify(simplifiedGraphqlError));
            });
    }
};

const handleErrorMessagesForUsers = (error: CombinedError, t: Translate) => {
    const parsedErrors = getUserFriendlyErrors(error, t as unknown as NextTranslate);

    if (parsedErrors.userError) {
        logException({
            message: error.message,
            parsedUserError: parsedErrors.userError,
            originalError: JSON.stringify(error),
            location: 'getErrorExchange.handleErrorMessagesForUsers',
        });
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
};

const logException = (e: unknown): void => {
    if (isEnvironment('development') || isWithErrorDebugging) {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    captureException(e);
};

export const isNotFoundError = (error: CombinedError | undefined) => {
    return !!error?.graphQLErrors.some(({ extensions }) => extensions.code === 404);
};
