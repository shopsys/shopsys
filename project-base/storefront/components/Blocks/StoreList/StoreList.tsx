import { TIDs } from 'cypress/tids';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { StoreListItem } from './StoreListItem';

type StoreListProps = {
    stores: StoreOrPacketeryPoint[];
    selectedStoreUuid: string | null;
    isDistanceFromSearchText: boolean;
    itemMode?: 'default' | 'selectOnItemClick';
    unknownDeliveryDateExplanation?: string;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreList: FC<StoreListProps> = ({
    stores,
    selectedStoreUuid,
    isDistanceFromSearchText,
    itemMode,
    unknownDeliveryDateExplanation,
    onSelectStoreCallback,
}) => {
    return (
        <div className="mt-2.5 flex flex-col gap-2.5" data-tid={TIDs.store_list}>
            {stores.map((store) => (
                <StoreListItem
                    key={store.identifier}
                    unknownDeliveryDateExplanation={unknownDeliveryDateExplanation}
                    isDistanceFromSearchText={isDistanceFromSearchText}
                    isSelected={store.identifier === selectedStoreUuid}
                    mode={itemMode}
                    store={store}
                    onSelectStoreCallback={onSelectStoreCallback}
                />
            ))}
        </div>
    );
};
