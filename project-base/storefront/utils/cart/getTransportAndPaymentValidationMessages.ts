import type { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import type { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import type { Maybe } from 'graphql/types';
import type { Translate } from 'next-translate';
import type { StoreOrPacketeryPoint } from 'utils/packetery/types';

export type TransportAndPaymentErrorsType = {
    transport: {
        name: 'transport';
        label: string;
        errorMessage: string | undefined;
    };
    payment: {
        name: 'payment';
        label: string;
        errorMessage: string | undefined;
    };
    goPaySwift: {
        name: 'goPaySwift';
        label: string;
        errorMessage: string | undefined;
    };
};

export const getTransportAndPaymentValidationMessages = (
    transport: Maybe<TypeTransportWithAvailablePaymentsFragment>,
    pickupPlace: Maybe<StoreOrPacketeryPoint>,
    payment: Maybe<TypeSimplePaymentFragment>,
    t: Translate,
) => {
    const errors: Partial<TransportAndPaymentErrorsType> = {};

    if (!transport) {
        errors.transport = {
            name: 'transport',
            label: t('Choose transport'),
            errorMessage: t('Please select transport'),
        };

        return errors;
    }

    if (transport.isPersonalPickup && !pickupPlace?.identifier) {
        errors.transport = {
            name: 'transport',
            label: t('Choose transport'),
            errorMessage: t('Please select transport with a personal pickup place'),
        };
    }
    if (!payment) {
        errors.payment = {
            name: 'payment',
            label: t('Choose payment'),
            errorMessage: t('Please select payment'),
        };
    }

    return errors;
};
