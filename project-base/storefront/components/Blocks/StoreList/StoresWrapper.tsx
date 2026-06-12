import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { useEffect, useState } from 'react';
import InfiniteScroll from 'react-infinite-scroll-component';
import { useSessionStore } from 'store/useSessionStore';
import { MapMarker } from 'types/map';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoresWrapperProps = {
    stores: TypeListedStoreConnectionFragment;
    isDistanceFromSearchText: boolean;
    isFetchingStores?: boolean;
    isLoadingMoreStores?: boolean;
    searchTextValue: string;
    selectedStoreUuid?: string | null;
    userCoordinates?: TypeCoordinates | null;
    scrollableTargetId?: string;
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
    searchTextValue,
    selectedStoreUuid,
    userCoordinates,
    scrollableTargetId,
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

    const edges = stores.edges || [];
    const mappedStores = mapConnectionEdges<StoreOrPacketeryPoint>(edges);
    const resolvedUserCoordinates = userCoordinates ?? internalUserCoordinates;

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
            (mappedStores?.some((store) => store.identifier === selectedStoreUuid) ?? false) ||
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

    if (!mappedStores) {
        return null;
    }

    const content = (
        <>
            {shouldShowTitle && <h1 className="mb-4">{t('Stores')}</h1>}

            <div className="flex flex-col-reverse gap-5 lg:flex-row">
                <div className="basis-1/2">
                    <SearchInput
                        ariaLabelForSearchButton={t('Search for a store', { ns: 'accessibility' })}
                        label={t('City or postcode')}
                        shouldShowSpinnerInInput={isFetchingStores}
                        value={searchTextValue}
                        onChange={(e) => onSearchTextCallback(e.currentTarget.value)}
                        onClear={() => onSearchTextCallback('')}
                    />
                    <InfiniteScroll
                        dataLength={mappedStores.length}
                        hasMore={shouldLoadMoreStores}
                        loader={<StoreListLoader />}
                        next={onLoadMoreStoresCallback ?? (() => undefined)}
                        scrollableTarget={scrollableTargetId}
                        style={{ overflow: 'visible' }}
                    >
                        <StoreList
                            isDistanceFromSearchText={isDistanceFromSearchText}
                            selectedStoreUuid={selectedStore}
                            stores={mappedStores}
                            onSelectStoreCallback={shouldAllowStoreSelection ? selectStoreHandler : undefined}
                        />
                    </InfiniteScroll>
                    {isLoadingMoreStores && <StoreListLoader />}
                </div>
                <div className="basis-1/2" data-tid={TIDs.stores_map}>
                    <div className="flex aspect-square rounded-xl bg-background-more p-5 lg:sticky lg:top-5">
                        <GoogleMap
                            activeMarkerHandler={clickOnMarkerHandler}
                            shouldCenterToUserCoordinates={searchTextValue === ''}
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
