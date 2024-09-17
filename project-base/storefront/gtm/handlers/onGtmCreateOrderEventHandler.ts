import { getGtmCreateOrderEvent } from 'gtm/factories/getGtmCreateOrderEvent';
import { GtmCreateOrderEventOrderPartType } from 'gtm/types/events';
import { GtmUserInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmCreateOrderEventHandler = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType | undefined,
    gtmCreateOrderEventUserPart: GtmUserInfoType | undefined,
    arePricesHidden: boolean,
    isPaymentSuccessful?: boolean,
): void => {
    if (gtmCreateOrderEventOrderPart === undefined || gtmCreateOrderEventUserPart === undefined) {
        return;
    }

    const gtmCreateOrderEvent = getGtmCreateOrderEvent(
        gtmCreateOrderEventOrderPart,
        gtmCreateOrderEventUserPart,
        arePricesHidden,
        isPaymentSuccessful,
    );

    gtmSafePushEvent(gtmCreateOrderEvent);
};
