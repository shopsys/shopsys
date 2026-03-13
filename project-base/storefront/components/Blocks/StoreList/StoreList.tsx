import { StoreListItem } from './StoreListItem';
import { TIDs } from 'cypress/tids';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoreListProps = {
    stores: StoreOrPacketeryPoint[];
    selectedStoreUuid: string | null;
};

export const StoreList: FC<StoreListProps> = ({ stores, selectedStoreUuid }) => {
    return (
        <div className="mt-2.5 flex flex-col gap-2.5" data-tid={TIDs.store_list}>
            {stores.map((store) => (
                <StoreListItem
                    key={store.identifier}
                    isSelected={store.identifier === selectedStoreUuid}
                    store={store}
                />
            ))}
        </div>
    );
};
