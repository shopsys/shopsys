import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { Error500Content } from 'components/Pages/ErrorPage/Error500Content';
import { logException } from 'helpers/errors/logException';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { ReactElement } from 'react';

const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

type ErrorPageProps = {
    hasGetInitialPropsRun: boolean;
    statusCode: number;
    props: Partial<ServerSidePropsType> | Promise<Partial<ServerSidePropsType>>;
    err: string;
};

const ErrorPage: NextPage<ErrorPageProps> = ({ hasGetInitialPropsRun, err, statusCode }): ReactElement => {
    if (!hasGetInitialPropsRun && err) {
        logException(err);
    }

    return statusCode === 404 ? <Error404Content /> : <Error500Content />;
};

ErrorPage.getInitialProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context: any) => {
    const middlewareStatusCode = Number.parseInt(context.res.getHeader(MIDDLEWARE_STATUS_CODE_KEY) || '');
    const middlewareStatusMessage = context.res.getHeader(MIDDLEWARE_STATUS_MESSAGE_KEY);

    const serverSideProps = await initServerSideProps({ context, redisClient, domainConfig, t });
    const statusCode = middlewareStatusCode || context.res.statusCode || 500;
    const err = middlewareStatusMessage || context.err || 'Unknown error (inside _error.tsx)';

    if (statusCode !== 404) {
        logException(err);
    }

    // eslint-disable-next-line require-atomic-updates
    context.res.statusCode = statusCode;
    const props: Partial<ServerSidePropsType> = 'props' in serverSideProps ? await serverSideProps.props : {};

    return {
        ...props,
        statusCode,
        err,
        hasGetInitialPropsRun: true,
    } as ErrorPageProps;
});

export default ErrorPage;
