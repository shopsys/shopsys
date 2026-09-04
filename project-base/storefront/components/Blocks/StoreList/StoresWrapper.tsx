import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { StoreSearchList } from 'components/Blocks/StoreList/StoreSearchList';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { useMemo, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { MapMarker } from 'types/map';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';
import { getStoreListMapFocus } from './getStoreListMapFocus';

type StoresWrapperProps = {
    stores: TypeListedStoreConnectionFragment | null;
    isDistanceFromSearchText: boolean;
    isFetchingStores?: boolean;
    isLoadingMoreStores?: boolean;
    variant?: 'page' | 'pickupSelection';
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
};

export const StoresWrapper: FC<StoresWrapperProps> = ({
    stores,
    isDistanceFromSearchText,
    isFetchingStores = false,
    isLoadingMoreStores = false,
    variant = 'page',
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
}) => {
    const isPickupSelectionVariant = variant === 'pickupSelection';
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const [internalSelectedStoreUuid, setInternalSelectedStoreUuid] = useState<string | null>(null);
    const { t } = useTranslation();
    const isControlledSelection = selectedStoreUuid !== undefined;
    const selectedStore = isControlledSelection ? (selectedStoreUuid ?? null) : internalSelectedStoreUuid;
    const shouldAllowStoreSelection = onSelectStoreCallback !== undefined;

    const mappedStores = useMemo(
        () => (stores === null ? null : mapConnectionEdges<StoreOrPacketeryPoint>(stores.edges || [])),
        [stores],
    );
    const displayedStores = useMemo(() => {
        if (mappedStores === null || mappedStores === undefined || priorityStore === null) {
            return mappedStores;
        }

        return [
            priorityStore,
            ...mappedStores.filter((mappedStore) => mappedStore.identifier !== priorityStore.identifier),
        ];
    }, [mappedStores, priorityStore]);
    const displayedStoreList = displayedStores ?? [];
    const loadedStoresCount = mappedStores?.length ?? 0;
    const resolvedUserCoordinates = userCoordinates ?? defaultUserCoordinates;
    const firstStore = displayedStores?.[0] ?? null;
    const searchCoordinatesForMapFocus =
        stores !== null && isDistanceFromSearchText && stores.searchCoordinates !== null
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

        onUserCoordinatesCallback({
            latitude: parseFloat(marker.latitude),
            longitude: parseFloat(marker.longitude),
        });

        if (searchTextValue !== '') {
            onSearchTextCallback('');
        }
    };

    const shouldShowMap = stores !== null;

    const content = (
        <div className={twMergeCustom('flex flex-col', isPickupSelectionVariant ? 'min-h-full' : 'min-h-0')}>
            {!isPickupSelectionVariant && <h1 className="mb-4">{t('Stores')}</h1>}

            <div
                className={twMergeCustom(
                    'flex vl:flex-row flex-col gap-5',
                    !isPickupSelectionVariant && 'min-h-0 flex-1',
                )}
            >
                <div className={twMergeCustom('vl:basis-1/2', !isPickupSelectionVariant && 'vl:min-h-0')}>
                    <StoreSearchList
                        displayedStores={displayedStoreList}
                        isDistanceFromSearchText={isDistanceFromSearchText}
                        isFetchingStores={isFetchingStores}
                        isLoadingMoreStores={isLoadingMoreStores}
                        itemMode={isPickupSelectionVariant ? 'selectOnItemClick' : 'default'}
                        loadedStoresCount={loadedStoresCount}
                        scrollableTargetId={scrollableTargetId}
                        searchTextValue={searchTextValue}
                        selectedStoreUuid={selectedStore}
                        storeConnectionErrorMessage={storeConnectionErrorMessage}
                        stores={stores}
                        onLoadMoreStoresCallback={onLoadMoreStoresCallback}
                        onSearchTextCallback={onSearchTextCallback}
                        onSelectStoreCallback={shouldAllowStoreSelection ? selectStoreHandler : undefined}
                    />
                </div>
                <div
                    className={twMergeCustom(
                        'vl:basis-1/2',
                        !isPickupSelectionVariant && 'vl:min-h-0',
                        !isPickupSelectionVariant && 'max-vl:order-first',
                    )}
                    data-tid={TIDs.stores_map}
                >
                    <div
                        className={twMergeCustom(
                            'vl:sticky flex rounded-xl bg-background-more p-5',
                            isPickupSelectionVariant
                                ? 'vl:top-0 h-72 vl:h-[min(520px,calc(80dvh-180px))]'
                                : 'vl:top-[calc(var(--sticky-navigation-offset,0px)+var(--spacing-5))] aspect-square',
                        )}
                    >
                        {shouldShowMap ? (
                            <GoogleMap
                                activeMarkerHandler={clickOnMarkerHandler}
                                additionalMarker={additionalMapMarker}
                                defaultZoom={mapFocus?.defaultZoom}
                                gestureHandling={isPickupSelectionVariant ? 'cooperative' : undefined}
                                latitude={mapLatitude}
                                longitude={mapLongitude}
                                markers={isPickupSelectionVariant ? displayedStoreList : undefined}
                                shouldCenterToUserCoordinates={shouldCenterMapToUserCoordinates}
                                userCoordinates={resolvedUserCoordinates}
                            />
                        ) : (
                            <Skeleton className="size-full rounded-lg bg-skeleton-less" />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );

    if (isPickupSelectionVariant) {
        return content;
    }

    return <Webline>{content}</Webline>;
};
