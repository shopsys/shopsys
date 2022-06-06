import { flush } from '@sentry/nextjs';
import Error500 from 'components/Pages/ErrorPage/500';
import { logException } from 'helpers/errors/logException';
import { ServerResponse } from 'http';
import NextErrorComponent from 'next/error';
import { ReactElement } from 'react';

const ErrorPage = ({ hasGetInitialPropsRun, err }: { hasGetInitialPropsRun: boolean; err?: any }): ReactElement => {
    if (!hasGetInitialPropsRun && err !== undefined && err !== null) {
        // getInitialProps is not called in case of
        // https://github.com/vercel/next.js/issues/8592. As a workaround, we pass
        // err via _app.js so it can be captured
        logException(err);
        // Flushing is not required in this case as it only happens on the client
    }

    return <Error500 />;
};

ErrorPage.getInitialProps = async ({ res, err, asPath }: { res: ServerResponse; err?: any; asPath: string }) => {
    const errorInitialProps: any = await NextErrorComponent.getInitialProps({ res, err } as any);

    // Workaround for https://github.com/vercel/next.js/issues/8592, mark when
    // getInitialProps has run
    errorInitialProps.hasGetInitialPropsRun = true;

    // Running on the server, the response object (`res`) is available.
    //
    // Next.js will pass an err on the server if a page's data fetching methods
    // threw or returned a Promise that rejected
    //
    // Running on the client (browser), Next.js will provide an err if:
    //
    //  - a page's `getInitialProps` threw or returned a Promise that rejected
    //  - an exception was thrown somewhere in the React lifecycle (render,
    //    componentDidMount, etc) that was caught by Next.js's React Error
    //    Boundary. Read more about what types of exceptions are caught by Error
    //    Boundaries: https://reactjs.org/docs/error-boundaries.html

    if (err !== undefined && err !== null) {
        logException(err);

        // Flushing before returning is necessary if deploying to Vercel, see
        // https://vercel.com/docs/platform/limits#streaming-responses
        await flush(2000);

        return errorInitialProps;
    }

    // If this point is reached, getInitialProps was called without any
    // information about what the error might be. This is unexpected and may
    // indicate a bug introduced in Next.js, so record it in Sentry
    logException(new Error(`_error.js getInitialProps missing data at path: ${asPath}`));
    await flush(2000);

    return errorInitialProps;
};

export default ErrorPage;
