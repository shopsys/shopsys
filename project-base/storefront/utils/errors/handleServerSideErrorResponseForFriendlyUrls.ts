import { isWithErrorDebugging } from './isWithErrorDebugging';
import { mapGraphqlErrorForDevelopment } from './mapGraphqlErrorForDevelopment';
import { IncomingMessage, ServerResponse } from 'http';
import { CombinedError } from 'urql';
import { getLoginUrlWithRedirect } from 'utils/auth/getLoginUrlWithRedirect';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const handleServerSideErrorResponseForFriendlyUrls = (
    error: CombinedError | undefined,
    serverSideRequestData: unknown,
    res: ServerResponse<IncomingMessage>,
    domainUrl: string,
) => {
    if (error?.response.status === 401) {
        const redirectTargetUrlWithLeadingSlash = getInternationalizedStaticUrls(['/login'], domainUrl)[0];
        return {
            redirect: {
                destination: getLoginUrlWithRedirect(redirectTargetUrlWithLeadingSlash, domainUrl),
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

    if (!serverSideRequestData && !(res.statusCode === 503)) {
        return {
            notFound: true as const,
        };
    }

    return null;
};
