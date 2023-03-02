import { TransportAndPaymentListItem } from '../TransportAndPaymentListItem';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { TransportAndPaymentSelectItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentSelectItemLabel/TransportAndPaymentSelectItemLabel';
import { TransportType } from 'types/transport';

type StoreSelectProps = {
    selectedStoreUuid: string;
    transport: TransportType;
    onSelectStoreCallback: (newStoreUuid: string | null) => void;
};

export const StoreSelect: FC<StoreSelectProps> = ({ selectedStoreUuid, transport, onSelectStoreCallback }) => {
    return (
        <ul>
            {transport.stores.map((pickupPlaceItem) => {
                return (
                    <TransportAndPaymentListItem
                        key={pickupPlaceItem.identifier}
                        isActive={selectedStoreUuid === pickupPlaceItem.identifier}
                    >
                        <Radiobutton
                            name="selectedStore"
                            id={pickupPlaceItem.identifier}
                            value={pickupPlaceItem.identifier}
                            checked={selectedStoreUuid === pickupPlaceItem.identifier}
                            onChangeCallback={onSelectStoreCallback}
                            label={
                                <TransportAndPaymentSelectItemLabel
                                    name={pickupPlaceItem.name}
                                    pickupPlaceDetail={pickupPlaceItem}
                                />
                            }
                        />
                    </TransportAndPaymentListItem>
                );
            })}
        </ul>
    );
};
