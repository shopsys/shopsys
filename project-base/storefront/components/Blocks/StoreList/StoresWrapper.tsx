import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { TIDs } from 'cypress/tids';
import { useStoresQuery } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { useCallback, useMemo, useState } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const StoresWrapper: FC = () => {
    const [{ data: storesData }] = useStoresQuery();
    const edges = storesData?.stores.edges || [];
    const mappedStores = useMemo(() => mapConnectionEdges<StoreOrPacketeryPoint>(edges), [edges]);

    const [selectedStore, setSelectedStore] = useState<string | null>(null);

    const clickOnMarkerHandler = useCallback(
        (uuid: string) => {
            setSelectedStore(uuid);
        },
        [mappedStores],
    );

    if (!mappedStores) {
        return null;
    }

    return (
        <div className="flex flex-col w-full lg:flex-row lg:gap-5">
            <div className="w-full lg:basis-1/2 max-lg:order-2 max-lg:mt-5">
                <StoreList selectedStoreUuid={selectedStore} stores={mappedStores} />
            </div>
            <div className="w-full lg:basis-1/2 max-lg:order-1">
                <div
                    className="flex aspect-square w-full mt-5 p-5 bg-backgroundMore rounded-xl lg:mt-0"
                    tid={TIDs.stores_map}
                >
                    <GoogleMap activeMarkerHandler={(uuid) => clickOnMarkerHandler(uuid)} markers={mappedStores} />
                </div>
            </div>
        </div>
    );
};
