import { TIDs } from 'cypress/tids';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { StoreListItem } from './StoreListItem';

type StoreListProps = {
    stores: StoreOrPacketeryPoint[];
    selectedStoreUuid: string | null;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreList: FC<StoreListProps> = ({ stores, selectedStoreUuid, onSelectStoreCallback }) => {
    return (
        <div className="mt-2.5 flex flex-col gap-2.5" data-tid={TIDs.store_list}>
            {stores.map((store) => (
                <StoreListItem
                    key={store.identifier}
                    isSelected={store.identifier === selectedStoreUuid}
                    store={store}
                    onSelectStoreCallback={onSelectStoreCallback}
                />
            ))}
        </div>
    );
};
