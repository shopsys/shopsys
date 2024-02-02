import { withScope, captureException } from '@sentry/nextjs';
import { isEnvironment } from 'helpers/isEnvironment';

export const logException = (e: unknown, extraData?: Record<string, unknown>): void => {
    if (isEnvironment('development')) {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    withScope((scope) => {
        if (extraData) {
            scope.setExtras(extraData);
        }
        captureException(e);
    });
};
