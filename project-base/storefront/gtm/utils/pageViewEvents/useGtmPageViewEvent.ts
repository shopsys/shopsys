import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmPageViewEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

export const useGtmPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType, areDataFetching?: boolean): void => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const lastViewedSlug = useRef<string>();
    const { setDidPageViewRun, isScriptLoaded } = useGtmContext();

    useEffect(() => {
        if (isScriptLoaded && gtmPageViewEvent._isLoaded && lastViewedSlug.current !== slug && !areDataFetching) {
            lastViewedSlug.current = slug;
            gtmSafePushEvent(gtmPageViewEvent);
            setDidPageViewRun(true);
        }
    }, [gtmPageViewEvent, areDataFetching, slug]);
};
