import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { Webline } from 'components/Layout/Webline/Webline';
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
    const { t } = useTranslation();

    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
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
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
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
        <Webline>
            <h1 className="mb-4">{t('Stores')}</h1>

            <div className="flex flex-col-reverse gap-5 lg:flex-row">
                <div className="basis-1/2">
                    <SearchInput
                        ariaLabelForSearchButton={t('Search for a store')}
                        label={t('City or postcode')}
                        shouldShowSpinnerInInput={isFetching}
                        value={searchTextValue}
                        onChange={(e) => setSearchTextValue(e.currentTarget.value)}
                        onClear={() => setSearchTextValue('')}
                    />
                    <StoreList selectedStoreUuid={selectedStore} stores={mappedStores} />
                </div>
                <div className="basis-1/2" data-tid={TIDs.stores_map}>
                    <div className="bg-background-more flex aspect-square rounded-xl p-5 lg:sticky lg:top-5">
                        <GoogleMap
                            activeMarkerHandler={(uuid) => clickOnMarkerHandler(uuid)}
                            markers={mappedStores}
                            shouldCenterToUserCoordinates={debouncedSearchTextValue === ''}
                            userCoordinates={userCoordinates}
                        />
                    </div>
                </div>
            </div>
        </Webline>
    );
};
