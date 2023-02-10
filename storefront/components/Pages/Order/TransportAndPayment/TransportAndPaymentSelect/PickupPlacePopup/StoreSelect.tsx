import { TransportAndPaymentListItem } from '../TransportAndPaymentListItem';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { TransportAndPaymentSelectItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentSelectItemLabel/TransportAndPaymentSelectItemLabel';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';

type StoreSelectProps = {
    selectedStoreUuid: string;
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi;
    onSelectStoreCallback: (newStoreUuid: string | null) => void;
};

export const StoreSelect: FC<StoreSelectProps> = ({ selectedStoreUuid, transport, onSelectStoreCallback }) => {
    return (
        <ul>
            {transport.stores?.edges?.map(
                (pickupPlaceItemEdge) =>
                    pickupPlaceItemEdge?.node !== null &&
                    pickupPlaceItemEdge?.node !== undefined && (
                        <TransportAndPaymentListItem
                            key={pickupPlaceItemEdge.node.identifier}
                            isActive={selectedStoreUuid === pickupPlaceItemEdge.node.identifier}
                        >
                            <Radiobutton
                                name="selectedStore"
                                id={pickupPlaceItemEdge.node.identifier}
                                value={pickupPlaceItemEdge.node.identifier}
                                checked={selectedStoreUuid === pickupPlaceItemEdge.node.identifier}
                                onChangeCallback={onSelectStoreCallback}
                                label={
                                    <TransportAndPaymentSelectItemLabel
                                        name={pickupPlaceItemEdge.node.name}
                                        pickupPlaceDetail={pickupPlaceItemEdge.node}
                                    />
                                }
                            />
                        </TransportAndPaymentListItem>
                    ),
            )}
        </ul>
    );
};
