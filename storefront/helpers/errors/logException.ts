import { captureException } from '@sentry/nextjs';

export const logException = (e: unknown): void => {
    if (process.env.NODE_ENV === 'development') {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    captureException(e);
};
