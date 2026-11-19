import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { useDisplayTimezone } from 'utils/formatting/useDisplayTimezone';
import { getPacketeryPointPickupDate } from 'utils/packetery/getPacketeryPointPickupDate';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { isPacketeryTransport } from 'utils/transport';

type TransportWithExpectedDeliveryDate = Pick<
    TypeTransportWithAvailablePaymentsFragment,
    'expectedDeliveryDate' | 'transportTypeCode'
>;

/**
 * Returns the expected delivery date of the transport; for Packeta with a selected pickup point,
 * the date respects the opening hours of the point, which only the storefront knows
 */
export const useTransportExpectedDeliveryDate = (
    transport: TransportWithExpectedDeliveryDate | null | undefined,
    selectedPickupPlace: StoreOrPacketeryPoint | null,
): string | null | undefined => {
    const timezone = useDisplayTimezone();
    const expectedDeliveryDate = transport?.expectedDeliveryDate;
    const isPacketeryPointSelected = !!selectedPickupPlace && isPacketeryTransport(transport?.transportTypeCode);

    if (!expectedDeliveryDate || !isPacketeryPointSelected) {
        return expectedDeliveryDate;
    }

    return getPacketeryPointPickupDate(expectedDeliveryDate, selectedPickupPlace.openingHours, timezone, new Date());
};
