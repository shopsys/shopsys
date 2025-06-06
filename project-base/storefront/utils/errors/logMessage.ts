import { isEnvironment } from 'utils/isEnvironment';
import { Sentry } from 'utils/sentry';

type SentryExtra = { key: string; data: string };

export const logMessage = (message: string, extras: Array<SentryExtra> = [], level: string = 'info'): void => {
    if (isEnvironment('development')) {
        /* eslint-disable no-console */
        console.warn(message, { extras });
    }

    Sentry.withScope((scope: any) => {
        extras.forEach((extra) => {
            scope.setExtra(extra.key, extra.data);
        });
        Sentry.captureMessage(message, level);
    });
};
