import { isWithErrorDebugging } from './isWithErrorDebugging';
import { isEnvironment } from 'utils/isEnvironment';
import { Sentry } from 'utils/sentry';

export const logException = (e: unknown): void => {
    if (isEnvironment('development') || isWithErrorDebugging) {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    Sentry.captureException(e);
};
