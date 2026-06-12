import { TIDs } from 'cypress/tids';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { StoreListItem } from './StoreListItem';

type StoreListProps = {
    stores: StoreOrPacketeryPoint[];
    selectedStoreUuid: string | null;
    isDistanceFromSearchText: boolean;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreList: FC<StoreListProps> = ({
    stores,
    selectedStoreUuid,
    isDistanceFromSearchText,
    onSelectStoreCallback,
}) => {
    return (
        <div className="mt-2.5 flex flex-col gap-2.5" data-tid={TIDs.store_list}>
            {stores.map((store) => (
                <StoreListItem
                    key={store.identifier}
                    isDistanceFromSearchText={isDistanceFromSearchText}
                    isSelected={store.identifier === selectedStoreUuid}
                    store={store}
                    onSelectStoreCallback={onSelectStoreCallback}
                />
            ))}
        </div>
    );
};
