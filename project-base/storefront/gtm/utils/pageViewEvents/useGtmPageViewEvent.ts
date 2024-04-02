import { useGtmContext } from 'gtm/context/useGtmContext';
import { GtmPageViewEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

export const useGtmPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType, fetching?: boolean): void => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const lastViewedSlug = useRef<string>();
    const { setDidPageViewRun } = useGtmContext();

    useEffect(() => {
        if (gtmPageViewEvent._isLoaded && lastViewedSlug.current !== slug && !fetching) {
            lastViewedSlug.current = slug;
            gtmSafePushEvent(gtmPageViewEvent);
            setDidPageViewRun(true);
        }
    }, [gtmPageViewEvent, fetching, slug]);
};
