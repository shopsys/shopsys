import { flush } from '@sentry/nextjs';
import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { Error500Content } from 'components/Pages/ErrorPage/Error500Content';
import { logException } from 'helpers/errors/logException';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import NextErrorComponent, { ErrorProps } from 'next/error';
import { ReactElement } from 'react';

const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

type ErrorPageProps = ErrorProps & {
    hasGetInitialPropsRun: boolean;
    err?: any;
};

const ErrorPage: NextPage<ErrorPageProps> = ({ hasGetInitialPropsRun, err, statusCode }): ReactElement => {
    if (!hasGetInitialPropsRun && err) {
        // getInitialProps is not called in case of
        // https://github.com/vercel/next.js/issues/8592. As a workaround, we pass
        // err via _app.js so it can be captured
        logException(err);
        // Flushing is not required in this case as it only happens on the client
    }

    return statusCode === 404 ? <Error404Content /> : <Error500Content />;
};

ErrorPage.getInitialProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context: any) => {
    const middlewareStatusCode = Number.parseInt(context.res.getHeader(MIDDLEWARE_STATUS_CODE_KEY) || '');
    const middlewareStatusMessage = context.res.getHeader(MIDDLEWARE_STATUS_MESSAGE_KEY);

    const statusCode = middlewareStatusCode || context.res.statusCode || 500;
    const errorInitialProps: any = await NextErrorComponent.getInitialProps({
        res: context.res,
        err: middlewareStatusMessage || context.err,
    } as any);
    const serverSideProps = await initServerSideProps({ context, redisClient, domainConfig, t });
    // Workaround for https://github.com/vercel/next.js/issues/8592, mark when
    // getInitialProps has run
    const errorPageProps = { ...errorInitialProps, hasGetInitialPropsRun: true };

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

    if (statusCode !== 404) {
        logException(context.err || new Error(`_error.js getInitialProps missing data at path: ${context.asPath}`));
    }

    // Flushing before returning is necessary if deploying to Vercel, see
    // https://vercel.com/docs/platform/limits#streaming-responses

    // eslint-disable-next-line require-atomic-updates
    context.res.statusCode = statusCode;
    await flush(2000);

    // eslint-disable-next-line require-atomic-updates
    const props = 'props' in serverSideProps ? serverSideProps.props : {};

    return {
        ...errorPageProps,
        ...{ ...props, statusCode },
    };
});

export default ErrorPage;
