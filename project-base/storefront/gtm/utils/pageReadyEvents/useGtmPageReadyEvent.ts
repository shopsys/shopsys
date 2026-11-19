import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

export const useGtmPageReadyEvent = (gtmPageReadyEvent: GtmPageReadyEventType, areDataFetching?: boolean): void => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const lastViewedSlug = useRef<string>(undefined);
    const { setDidPageReadyRun, isScriptLoaded } = useGtmContext();
    // the customer loads on the client, until then the event would describe a logged-in customer as a visitor without a cart
    const [{ fetching: isCurrentCustomerUserFetching, stale: isCurrentCustomerUserStale }] =
        useCurrentCustomerUserQuery();
    const isCurrentCustomerUserResolved = !isCurrentCustomerUserFetching && !isCurrentCustomerUserStale;

    useEffect(() => {
        const canPushPageReadyEvent =
            isScriptLoaded && gtmPageReadyEvent._isLoaded && isCurrentCustomerUserResolved && !areDataFetching;

        if (canPushPageReadyEvent && lastViewedSlug.current !== slug) {
            lastViewedSlug.current = slug;
            gtmSafePushEvent(gtmPageReadyEvent);
            setDidPageReadyRun(true);
        }
    }, [gtmPageReadyEvent, areDataFetching, isCurrentCustomerUserResolved, slug, isScriptLoaded, setDidPageReadyRun]);
};
