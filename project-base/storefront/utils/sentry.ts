/* eslint-disable @typescript-eslint/no-unused-vars, no-console, @typescript-eslint/no-require-imports */
// TODO: mock Sentry, when turned off - could this be usefull?
let Sentry = {
    init: (_options: any) => void 0,
    captureException: (e: unknown) => console.log(e),
    captureMessage: (m: string, _context?: unknown) => console.log(m),
    captureRequestError: (_error: any) => void 0,
    replayIntegration: () => ({}),
    captureRouterTransitionStart: (_data: any) => void 0,
    withScope: (_scope: any) => void 0,
};

if (process.env.APP_ENV === 'production') {
    Sentry = require('@sentry/nextjs');
}

export { Sentry };
