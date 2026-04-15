import { GetServerSidePropsContext } from 'next';
import { CombinedError } from 'urql';
import { getLoginUrlWithRedirect } from 'utils/auth/getLoginUrlWithRedirect';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { isExpectedPriceFilterError } from './expectedErrors';
import { isWithErrorDebugging } from './isWithErrorDebugging';
import { mapGraphqlErrorForDevelopment } from './mapGraphqlErrorForDevelopment';

export const handleServerSideErrorResponseForFriendlyUrls = (
    error: CombinedError | undefined,
    serverSideRequestData: unknown,
    context: GetServerSidePropsContext,
    domainUrl: string,
    urlSlug: string | undefined = undefined,
) => {
    if (error?.response.status === 401) {
        const redirectTargetUrlWithLeadingSlash = getInternationalizedStaticUrls(['/login'], domainUrl)[0];

        return {
            redirect: {
                destination: getLoginUrlWithRedirect(redirectTargetUrlWithLeadingSlash, domainUrl, context),
                permanent: false,
            },
        };
    }

    if (error?.graphQLErrors.some((error) => error.extensions.code === 500)) {
        if (isWithErrorDebugging) {
            throw new Error(JSON.stringify(mapGraphqlErrorForDevelopment(error.graphQLErrors[0])));
        }

        throw new Error('Internal Server Error');
    }

    if (isExpectedPriceFilterError(error)) {
        return {
            redirect: {
                destination: `${domainUrl}/${urlSlug}`,
                permanent: false,
            },
        };
    }

    if (!serverSideRequestData && !(context.res.statusCode === 503)) {
        return {
            notFound: true as const,
        };
    }

    return null;
};
