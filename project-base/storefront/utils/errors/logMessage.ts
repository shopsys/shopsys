import { captureMessage, SeverityLevel, withScope } from '@sentry/nextjs';
import { isEnvironment } from 'utils/isEnvironment';

type SentryExtra = { key: string; data: string };

export const logMessage = (message: string, extras: Array<SentryExtra> = [], level: SeverityLevel = 'info'): void => {
    if (isEnvironment('development')) {
        /* eslint-disable no-console */
        console.warn(message, { extras });
    }

    withScope((scope) => {
        extras.forEach((extra) => {
            scope.setExtra(extra.key, extra.data);
        });
        captureMessage(message, level);
    });
};
