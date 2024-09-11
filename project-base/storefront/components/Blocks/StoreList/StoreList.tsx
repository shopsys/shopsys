import { StoreListItem } from './StoreListItem';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoreListProps = {
    stores: StoreOrPacketeryPoint[];
    selectedStoreUuid: string | null;
};

export const StoreList: FC<StoreListProps> = ({ stores, selectedStoreUuid }) => {
    return (
        <div className="flex flex-col gap-2.5">
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
