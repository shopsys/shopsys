// TODO: mock Sentry, when turned off - could this be usefull?
let Sentry = {
    init: (any) => {},
    captureException: (e) => console.log(e),
    captureMessage: (m, c) => console.log(m),
    captureRequestError: (any) => {},
    replayIntegration: () => {},
    captureRouterTransitionStart: (any) => {},
    withScope: (scope: any) => {},
};

if (process.env.APP_ENV === 'production') {
    Sentry = require('@sentry/nextjs');
}

export { Sentry };
