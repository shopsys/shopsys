import { captureException } from '@sentry/nextjs';
import { isEnvironment } from 'utils/isEnvironment';
import { isWithErrorDebugging } from './isWithErrorDebugging';

export const logException = (e: unknown): void => {
    if (isEnvironment('development') || isWithErrorDebugging) {
        // biome-ignore lint/suspicious/noConsole: intentional error logging in development
        console.error(e);
    }

    captureException(e);
};
