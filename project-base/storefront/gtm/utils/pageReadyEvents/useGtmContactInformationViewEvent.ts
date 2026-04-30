import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmContactInformationViewEvent } from 'gtm/factories/getGtmContactInformationViewEvent';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmContactInformationViewEvent = (gtmPageReadyEvent: GtmPageReadyEventType): void => {
    const wasViewedRef = useRef(false);
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();
    const { canSeePrices } = useAuthorization();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageReadyRun &&
            gtmPageReadyEvent._isLoaded &&
            gtmPageReadyEvent.cart &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            gtmSafePushEvent(getGtmContactInformationViewEvent(gtmPageReadyEvent.cart, !canSeePrices));
        }
    }, [gtmPageReadyEvent._isLoaded, gtmPageReadyEvent.cart, didPageReadyRun, isScriptLoaded, canSeePrices]);
};
