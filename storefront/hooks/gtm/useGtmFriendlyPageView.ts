import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm';

export const useGtmFriendlyPageView = (event: GtmPageViewEventType, slug: string, fetching: boolean): void => {
    const lastViewedFriendlyPageSlug = useRef<string | undefined>(undefined);

    useEffect(() => {
        if (event._isLoaded && lastViewedFriendlyPageSlug.current !== slug && !fetching) {
            lastViewedFriendlyPageSlug.current = slug;
            gtmSafePushEvent(event);
        }
    }, [event, slug, fetching]);
};
