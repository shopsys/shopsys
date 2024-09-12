import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { TIDs } from 'cypress/tids';
import { useStoresQuery } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { TypeCoordinates } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { useDebounce } from 'utils/useDebounce';

export const StoresWrapper: FC = () => {
    const [searchTextValue, setSearchTextValue] = useState<string>('');
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const updateDefaultUserCoordinates = useSessionStore((s) => s.updateCoordinates);
    const [userCoordinates, setUserCoordinates] = useState<TypeCoordinates | null>(defaultUserCoordinates);
    const [selectedStore, setSelectedStore] = useState<string | null>(null);

    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
    const { t } = useTranslation();

    const [{ data: storesData, fetching: isFetching }] = useStoresQuery({
        variables: {
            searchText: debouncedSearchTextValue || null,
            coordinates: userCoordinates,
        },
    });
    const edges = storesData?.stores.edges || [];
    const mappedStores = useMemo(() => mapConnectionEdges<StoreOrPacketeryPoint>(edges), [edges]);

    useEffect(() => {
        navigator.geolocation.getCurrentPosition((position) => {
            const coordinates: TypeCoordinates = {
                latitude: position.coords.latitude.toString(),
                longitude: position.coords.longitude.toString(),
            };
            setUserCoordinates(coordinates);
            updateDefaultUserCoordinates(coordinates);
        });
    }, []);

    const clickOnMarkerHandler = useCallback((uuid: string) => {
        setSelectedStore(uuid);
    }, []);

    if (!mappedStores) {
        return null;
    }

    return (
        <div className="flex flex-col w-full lg:flex-row lg:gap-5">
            <div className="w-full lg:basis-1/2 max-lg:order-2 max-lg:mt-5">
                <SearchInput
                    label={t('City or postcode')}
                    shouldShowSpinnerInInput={isFetching}
                    value={searchTextValue}
                    onChange={(e) => setSearchTextValue(e.currentTarget.value)}
                    onClear={() => setSearchTextValue('')}
                />
                <StoreList selectedStoreUuid={selectedStore} stores={mappedStores} />
            </div>
            <div className="w-full lg:basis-1/2 max-lg:order-1" tid={TIDs.stores_map}>
                <div className="flex aspect-square w-full mt-5 p-5 bg-backgroundMore rounded-xl lg:mt-0 lg:sticky lg:top-5">
                    <GoogleMap
                        activeMarkerHandler={(uuid) => clickOnMarkerHandler(uuid)}
                        markers={mappedStores}
                        shouldCenterToUserCoordinates={debouncedSearchTextValue === ''}
                        userCoordinates={userCoordinates}
                    />
                </div>
            </div>
        </div>
    );
};
