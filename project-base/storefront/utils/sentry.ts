// TODO: mock Sentry, when turned off - could this be usefull?
let Sentry = {
    init: (any) => {},
    captureException: (e) => console.log(e),
    captureRequestError: (any) => {},
    replayIntegration: () => {},
    captureRouterTransitionStart: (any) => {},
};

if (process.env.APP_ENV === 'production') {
    Sentry = require('@sentry/nextjs');
}

export { Sentry };
