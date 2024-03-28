import { SimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { getGtmPaymentChangeEvent } from 'gtm/factories/getGtmPaymentChangeEvent';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';
import { GtmCartInfoType } from 'gtm/types/objects';

export const onGtmPaymentChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedPayment: SimplePaymentFragment | null,
): void => {
    if (gtmCartInfo && updatedPayment !== null) {
        gtmSafePushEvent(getGtmPaymentChangeEvent(gtmCartInfo, updatedPayment));
    }
};
