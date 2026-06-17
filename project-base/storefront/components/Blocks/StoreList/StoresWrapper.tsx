import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { useEffect, useMemo, useState } from 'react';
import InfiniteScroll from 'react-infinite-scroll-component';
import { useSessionStore } from 'store/useSessionStore';
import { MapMarker } from 'types/map';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { getStoreListMapFocus } from './getStoreListMapFocus';
import { StoreListError } from './StoreListError';

type StoresWrapperProps = {
    stores: TypeListedStoreConnectionFragment;
    isDistanceFromSearchText: boolean;
    isFetchingStores?: boolean;
    isLoadingMoreStores?: boolean;
    appliedSearchTextValue: string;
    searchTextValue: string;
    priorityStore?: StoreOrPacketeryPoint | null;
    selectedStoreUuid?: string | null;
    userCoordinates?: TypeCoordinates | null;
    scrollableTargetId?: string;
    storeConnectionErrorMessage?: string;
    onLoadMoreStoresCallback?: () => void;
    onSearchTextCallback: (searchText: string) => void;
    onUserCoordinatesCallback?: (coordinates: TypeCoordinates | null) => void;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
    shouldShowTitle?: boolean;
    shouldWrapInWebline?: boolean;
};

export const StoresWrapper: FC<StoresWrapperProps> = ({
    stores,
    isDistanceFromSearchText,
    isFetchingStores = false,
    isLoadingMoreStores = false,
    appliedSearchTextValue,
    searchTextValue,
    priorityStore = null,
    selectedStoreUuid,
    userCoordinates,
    scrollableTargetId,
    storeConnectionErrorMessage,
    onLoadMoreStoresCallback,
    onSearchTextCallback,
    onUserCoordinatesCallback,
    onSelectStoreCallback,
    shouldShowTitle = true,
    shouldWrapInWebline = true,
}) => {
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const updateDefaultUserCoordinates = useSessionStore((s) => s.updateCoordinates);
    const [internalUserCoordinates, setInternalUserCoordinates] = useState<TypeCoordinates | null>(
        defaultUserCoordinates,
    );
    const [internalSelectedStoreUuid, setInternalSelectedStoreUuid] = useState<string | null>(null);
    const { t } = useTranslation();
    const isControlledSelection = selectedStoreUuid !== undefined;
    const selectedStore = isControlledSelection ? (selectedStoreUuid ?? null) : internalSelectedStoreUuid;
    const shouldAllowStoreSelection = onSelectStoreCallback !== undefined;
    const shouldLoadMoreStores =
        stores.pageInfo.hasNextPage &&
        !isFetchingStores &&
        !isLoadingMoreStores &&
        onLoadMoreStoresCallback !== undefined;

    const mappedStores = useMemo(() => mapConnectionEdges<StoreOrPacketeryPoint>(stores.edges || []), [stores.edges]);
    const displayedStores = useMemo(() => {
        if (mappedStores === undefined || priorityStore === null) {
            return mappedStores;
        }

        return [
            priorityStore,
            ...mappedStores.filter((mappedStore) => mappedStore.identifier !== priorityStore.identifier),
        ];
    }, [mappedStores, priorityStore]);
    const loadedStoresCount = mappedStores?.length ?? 0;
    const resolvedUserCoordinates = userCoordinates ?? internalUserCoordinates;
    const firstStore = displayedStores?.[0] ?? null;
    const searchCoordinatesForMapFocus =
        isDistanceFromSearchText && stores.searchCoordinates !== null
            ? {
                  latitude: stores.searchCoordinates.latitude,
                  longitude: stores.searchCoordinates.longitude,
              }
            : null;
    const mapFocus = getStoreListMapFocus(searchCoordinatesForMapFocus, firstStore);
    const additionalMapMarker = mapFocus?.searchCoordinatesMarker
        ? {
              name: appliedSearchTextValue.trim(),
              latitude: String(mapFocus.searchCoordinatesMarker.latitude),
              longitude: String(mapFocus.searchCoordinatesMarker.longitude),
          }
        : null;
    const mapLatitude = mapFocus !== null ? String(mapFocus.latitude) : null;
    const mapLongitude = mapFocus !== null ? String(mapFocus.longitude) : null;
    const shouldCenterMapToUserCoordinates = mapFocus === null && searchTextValue === '';

    useEffect(() => {
        if (!navigator.geolocation) {
            return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
            const coordinates: TypeCoordinates = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            };
            setInternalUserCoordinates(coordinates);
            updateDefaultUserCoordinates(coordinates);
            onUserCoordinatesCallback?.(coordinates);
        });
    }, [onUserCoordinatesCallback, updateDefaultUserCoordinates]);

    const selectStoreHandler = (uuid: string | null) => {
        if (!isControlledSelection) {
            setInternalSelectedStoreUuid(uuid);
        }

        onSelectStoreCallback?.(uuid);
    };

    const clickOnMarkerHandler = (marker: MapMarker | null) => {
        if (marker === null) {
            selectStoreHandler(null);

            return;
        }

        const selectedStoreUuid = marker.identifier ?? null;
        selectStoreHandler(selectedStoreUuid);

        if (
            selectedStoreUuid === null ||
            (displayedStores?.some((store) => store.identifier === selectedStoreUuid) ?? false) ||
            onUserCoordinatesCallback === undefined
        ) {
            return;
        }

        const markerCoordinates = {
            latitude: parseFloat(marker.latitude),
            longitude: parseFloat(marker.longitude),
        };

        setInternalUserCoordinates(markerCoordinates);
        onUserCoordinatesCallback(markerCoordinates);

        if (searchTextValue !== '') {
            onSearchTextCallback('');
        }
    };

    const searchInput = (
        <SearchInput
            ariaLabelForSearchButton={t('Search for a store', { ns: 'accessibility' })}
            label={t('City or postcode')}
            shouldShowSpinnerInInput={isFetchingStores}
            value={searchTextValue}
            onChange={(e) => onSearchTextCallback(e.currentTarget.value)}
            onClear={() => onSearchTextCallback('')}
        />
    );

    if (storeConnectionErrorMessage !== undefined) {
        const errorContent = (
            <>
                {shouldShowTitle && <h1 className="mb-4">{t('Stores')}</h1>}

                <div className="max-w-xl">
                    {searchInput}
                    <StoreListError message={storeConnectionErrorMessage} />
                </div>
            </>
        );

        if (!shouldWrapInWebline) {
            return errorContent;
        }

        return <Webline>{errorContent}</Webline>;
    }

    if (!displayedStores) {
        return null;
    }

    const content = (
        <>
            {shouldShowTitle && <h1 className="mb-4">{t('Stores')}</h1>}

            <div className="flex flex-col-reverse gap-5 lg:flex-row">
                <div className="basis-1/2">
                    {searchInput}
                    <InfiniteScroll
                        dataLength={loadedStoresCount}
                        hasMore={shouldLoadMoreStores}
                        loader={<StoreListLoader />}
                        next={onLoadMoreStoresCallback ?? (() => undefined)}
                        scrollableTarget={scrollableTargetId}
                        style={{ overflow: 'visible' }}
                    >
                        <StoreList
                            isDistanceFromSearchText={isDistanceFromSearchText}
                            selectedStoreUuid={selectedStore}
                            stores={displayedStores}
                            onSelectStoreCallback={shouldAllowStoreSelection ? selectStoreHandler : undefined}
                        />
                    </InfiniteScroll>
                    {isLoadingMoreStores && <StoreListLoader />}
                </div>
                <div className="basis-1/2" data-tid={TIDs.stores_map}>
                    <div className="flex aspect-square rounded-xl bg-background-more p-5 lg:sticky lg:top-5">
                        <GoogleMap
                            activeMarkerHandler={clickOnMarkerHandler}
                            additionalMarker={additionalMapMarker}
                            defaultZoom={mapFocus?.defaultZoom}
                            latitude={mapLatitude}
                            longitude={mapLongitude}
                            shouldCenterToUserCoordinates={shouldCenterMapToUserCoordinates}
                            userCoordinates={resolvedUserCoordinates}
                        />
                    </div>
                </div>
            </div>
        </>
    );

    if (!shouldWrapInWebline) {
        return content;
    }

    return <Webline>{content}</Webline>;
};

const StoreListLoader: FC = () => {
    const { t } = useTranslation();

    return <div className="mt-2.5 text-center text-sm text-text-less">{t('Loading more stores')}</div>;
};
