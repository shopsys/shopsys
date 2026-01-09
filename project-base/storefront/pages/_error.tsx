import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { Error500Content } from 'components/Pages/ErrorPage/Error500Content';
import { MinimalErrorContent } from 'components/Pages/ErrorPage/MinimalErrorContent';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GetServerSidePropsContext, NextPage, NextPageContext } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { ReactElement } from 'react';
import { CookiesStoreState, getCookiesStoreState } from 'utils/cookies/cookiesStore';
import { DomainConfigType, getDomainConfig } from 'utils/domain/domainConfig';
import { isWithErrorDebugging } from 'utils/errors/isWithErrorDebugging';
import { logException } from 'utils/errors/logException';

type ErrorPageProps = {
    statusCode: number;
    err?: string;
    domainConfig?: DomainConfigType;
    cookiesStore?: CookiesStoreState;
    customerUserRoles?: TypeCustomerUserRoleEnum[];
};

const ErrorPage: NextPage<ErrorPageProps> = ({ statusCode, err, domainConfig }): ReactElement => {
    // If domainConfig prop exists, _app.tsx will set up providers and we can use full error pages
    // If not, render minimal page (no dependencies on providers)
    if (!domainConfig) {
        return <MinimalErrorContent err={err} showDebugInfo={isWithErrorDebugging} statusCode={statusCode} />;
    }

    return statusCode === 404 ? <Error404Content /> : <Error500Content err={err} />;
};

ErrorPage.getInitialProps = async (context: NextPageContext): Promise<ErrorPageProps> => {
    const statusCode = context.res?.statusCode ?? (context.err as any)?.statusCode ?? 500;

    let errorMessage: string | undefined;
    if (context.err) {
        errorMessage =
            context.err instanceof Error
                ? JSON.stringify({ name: context.err.name, message: context.err.message, stack: context.err.stack })
                : String(context.err);
    }

    if (statusCode !== 404) {
        logException({
            message: errorMessage ?? 'Unknown error',
            statusCode,
            location: 'ErrorPage.getInitialProps',
        });
    }

    // Server-side: Try to get domain config for full rendering with layout
    // Client-side (no res): Fall back to minimal rendering (ErrorBoundary handles client errors)
    const isRuntimeError = !!context.err;

    if (context.res && context.req && !isRuntimeError) {
        try {
            const domainConfig = getDomainConfig(context);
            // getCookiesStoreState only uses req/res from context, which exist on NextPageContext
            const cookiesStoreState = getCookiesStoreState(domainConfig, {
                req: context.req,
                res: context.res,
            } as GetServerSidePropsContext);

            // Set proper status code on response
            context.res.statusCode = statusCode;

            // Load translations for the correct locale
            const translations = await loadNamespaces({
                locale: domainConfig.defaultLocale,
                pathname: '/_error',
                namespaces: ['common', 'accessibility'],
            });

            return {
                ...translations,
                statusCode,
                err: errorMessage,
                domainConfig,
                cookiesStore: cookiesStoreState,
                customerUserRoles: [],
            };
        } catch (e) {
            // getDomainConfig failed (e.g., unknown domain) - fall back to minimal rendering
            logException({
                message: e instanceof Error ? e.message : String(e),
                statusCode,
                location: 'ErrorPage.getInitialProps.getDomainConfig',
            });
        }
    }

    // Client-side or domain config failed: minimal props
    return {
        statusCode,
        err: errorMessage,
    };
};

export default ErrorPage;
