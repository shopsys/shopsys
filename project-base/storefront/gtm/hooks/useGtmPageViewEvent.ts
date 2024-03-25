import { useGtmContext } from 'gtm/context/useGtmContext';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';
import { GtmPageViewEventType } from 'gtm/types/events';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';

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
