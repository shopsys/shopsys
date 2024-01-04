import { captureException } from '@sentry/nextjs';
import { isEnvironment } from 'helpers/isEnvironment';

export const logException = (e: unknown): void => {
    if (isEnvironment('development')) {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    captureException(e);
};
