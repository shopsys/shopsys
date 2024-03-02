import { isWithErrorDebugging } from './isWithErrorDebugging';
import { captureException } from '@sentry/nextjs';
import { isEnvironment } from 'helpers/isEnvironment';

export const logException = (e: unknown): void => {
    if (isEnvironment('development') || isWithErrorDebugging) {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    captureException(e);
};
