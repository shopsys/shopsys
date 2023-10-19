import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { TransportAndPaymentSelectItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentSelectItemLabel';
import { ListedStoreFragmentApi, TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useMemo } from 'react';

type StoreSelectProps = {
    selectedStoreUuid: string;
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi;
    onSelectStoreCallback: (newStoreUuid: string | null) => void;
};

export const StoreSelect: FC<StoreSelectProps> = ({ selectedStoreUuid, transport, onSelectStoreCallback }) => {
    const mappedStores = useMemo(
        () => mapConnectionEdges<ListedStoreFragmentApi>(transport.stores?.edges),
        [transport.stores?.edges],
    );

    return (
        <ul>
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
                        onChangeCallback={onSelectStoreCallback}
                    />
                </TransportAndPaymentListItem>
            ))}
        </ul>
    );
};
