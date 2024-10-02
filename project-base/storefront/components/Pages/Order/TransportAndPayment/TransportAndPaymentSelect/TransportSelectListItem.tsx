import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { useTransportChangeInSelect } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeTransportStoresFragment } from 'graphql/requests/transports/fragments/TransportStoresFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { memo } from 'react';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type ChangeTransport = ReturnType<typeof useTransportChangeInSelect>['changeTransport'];

type TransportListItemProps = {
    transport:
        | (TypeTransportWithAvailablePaymentsFragment & TypeTransportStoresFragment)
        | TypeTransportWithAvailablePaymentsFragment;
    isActive?: boolean;
    changeTransport: ChangeTransport;
    pickupPlace: StoreOrPacketeryPoint | null;
};

const TransportListItemComp: FC<TransportListItemProps> = ({
    transport,
    isActive = false,
    changeTransport,
    pickupPlace,
}) => (
    <TransportAndPaymentListItem key={transport.uuid} isActive={isActive}>
        <Radiobutton
            checked={isActive}
            id={transport.uuid}
            name="transport"
            value={transport.uuid}
            label={
                <TransportAndPaymentSelectItemLabel
                    daysUntilDelivery={transport.daysUntilDelivery}
                    description={transport.description}
                    image={transport.mainImage}
                    isSelected={isActive}
                    name={transport.name}
                    pickupPlaceDetail={isActive && pickupPlace ? pickupPlace : undefined}
                    price={transport.price}
                />
            }
            onClick={changeTransport}
        />
    </TransportAndPaymentListItem>
);

export const TransportListItem = memo(TransportListItemComp);
