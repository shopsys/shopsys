import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { StoreListItem } from './StoreListItem';

export const StoreList: FC<{ stores: StoreOrPacketeryPoint[] }> = ({ stores }) => {
    return (
        <div className="flex flex-col gap-2.5 h-[614px] overflow-scroll">
            {stores.map((store) => (
                <StoreListItem key={store.identifier} store={store} />
            ))}
        </div>
    );
};
