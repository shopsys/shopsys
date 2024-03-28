import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { TransportAndPaymentSelectItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentSelectItemLabel';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

type StoreSelectProps = {
    selectedStoreUuid: string;
    transport: TypeTransportWithAvailablePaymentsAndStoresFragment;
    onSelectStoreCallback: (newStoreUuid: string | null) => void;
};

export const StoreSelect: FC<StoreSelectProps> = ({ selectedStoreUuid, transport, onSelectStoreCallback }) => {
    const mappedStores = useMemo(
        () => mapConnectionEdges<TypeListedStoreFragment>(transport.stores?.edges),
        [transport.stores?.edges],
    );

    return (
        <ul className="max-h-[70dvh] overflow-y-auto">
            {mappedStores?.map((pickupPlace) => (
                <TransportAndPaymentListItem
                    key={pickupPlace.identifier}
                    isActive={selectedStoreUuid === pickupPlace.identifier}
                >
                    <Radiobutton
                        checked={selectedStoreUuid === pickupPlace.identifier}
                        id={pickupPlace.identifier}
                        name="selectedStore"
                        value={pickupPlace.identifier}
                        label={
                            <TransportAndPaymentSelectItemLabel
                                name={pickupPlace.name}
                                pickupPlaceDetail={pickupPlace}
                            />
                        }
                        onChange={(event) => onSelectStoreCallback(event.target.value)}
                    />
                </TransportAndPaymentListItem>
            ))}
        </ul>
    );
};
