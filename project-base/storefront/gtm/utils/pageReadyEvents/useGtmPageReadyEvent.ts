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

    useEffect(() => {
        if (isScriptLoaded && gtmPageReadyEvent._isLoaded && lastViewedSlug.current !== slug && !areDataFetching) {
            lastViewedSlug.current = slug;
            gtmSafePushEvent(gtmPageReadyEvent);
            setDidPageReadyRun(true);
        }
    }, [gtmPageReadyEvent, areDataFetching, slug, isScriptLoaded, setDidPageReadyRun]);
};
