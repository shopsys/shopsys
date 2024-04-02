import { SimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { getGtmPaymentChangeEvent } from 'gtm/factories/getGtmPaymentChangeEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmPaymentChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedPayment: SimplePaymentFragment | null,
): void => {
    if (gtmCartInfo && updatedPayment !== null) {
        gtmSafePushEvent(getGtmPaymentChangeEvent(gtmCartInfo, updatedPayment));
    }
};
