import { getLoginUrlWithRedirect } from './getLoginUrlWithRedirect';
import { GetServerSidePropsContext, Redirect } from 'next';

export const getUnauthenticatedRedirectSSR = (
    resolvedUrl: string,
    domainUrl: string,
    context: GetServerSidePropsContext,
): { redirect: Redirect } => {
    return {
        redirect: {
            statusCode: 302,
            destination: getLoginUrlWithRedirect(resolvedUrl, domainUrl, context),
        },
    };
};
