import { StoreListItem } from './StoreListItem';
import { useEffect, useRef } from 'react';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const StoreList: FC<{ stores: StoreOrPacketeryPoint[]; selectedStoreUuid: string | null }> = ({
    stores,
    selectedStoreUuid,
}) => {
    const storeRefs = useRef<{ [key: string]: HTMLDivElement | null }>({});

    useEffect(() => {
        if (selectedStoreUuid && storeRefs.current[selectedStoreUuid]) {
            setTimeout(() => {
                storeRefs.current[selectedStoreUuid]?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center',
                });
            }, 100);
        }
    }, [selectedStoreUuid]);

    return (
        <div className="flex flex-col gap-2.5">
            {stores.map((store) => (
                <div key={store.identifier} ref={(el) => (storeRefs.current[store.identifier] = el)}>
                    <StoreListItem
                        key={store.identifier}
                        isSelected={store.identifier === selectedStoreUuid}
                        store={store}
                    />
                </div>
            ))}
        </div>
    );
};
