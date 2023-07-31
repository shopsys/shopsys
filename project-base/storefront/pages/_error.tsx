import { flush } from '@sentry/nextjs';
import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { Error500Content } from 'components/Pages/ErrorPage/Error500Content';
import { logException } from 'helpers/errors/logException';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { NextPage } from 'next';
import NextErrorComponent, { ErrorProps } from 'next/error';
import { ReactElement } from 'react';

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
    const errorInitialProps: any = await NextErrorComponent.getInitialProps({
        res: context.res,
        err: context.err,
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

    if (context.err) {
        logException(context.err);
    } else {
        // If this point is reached, getInitialProps was called without any
        // information about what the error might be. This is unexpected and may
        // indicate a bug introduced in Next.js, so record it in Sentry
        logException(new Error(`_error.js getInitialProps missing data at path: ${context.asPath}`));
    }

    // Flushing before returning is necessary if deploying to Vercel, see
    // https://vercel.com/docs/platform/limits#streaming-responses
    await flush(2000);

    return {
        ...errorPageProps,
        ...('props' in serverSideProps ? serverSideProps.props : {}),
    };
});

export default ErrorPage;
