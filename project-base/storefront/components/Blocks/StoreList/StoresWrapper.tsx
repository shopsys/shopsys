import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { StoreList } from 'components/Blocks/StoreList/StoreList';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { useEffect, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoresWrapperProps = {
    stores: TypeListedStoreConnectionFragment;
    isFetchingStores?: boolean;
    searchTextValue: string;
    selectedStoreUuid?: string | null;
    userCoordinates?: TypeCoordinates | null;
    onSearchTextCallback: (searchText: string) => void;
    onUserCoordinatesCallback?: (coordinates: TypeCoordinates | null) => void;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
    shouldShowTitle?: boolean;
    shouldWrapInWebline?: boolean;
};

export const StoresWrapper: FC<StoresWrapperProps> = ({
    stores,
    isFetchingStores = false,
    searchTextValue,
    selectedStoreUuid,
    userCoordinates,
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

    const clickOnMarkerHandler = (uuid: string) => {
        selectStoreHandler(uuid === '' ? null : uuid);
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
                    <StoreList
                        selectedStoreUuid={selectedStore}
                        stores={mappedStores}
                        onSelectStoreCallback={shouldAllowStoreSelection ? selectStoreHandler : undefined}
                    />
                </div>
                <div className="basis-1/2" data-tid={TIDs.stores_map}>
                    <div className="flex aspect-square rounded-xl bg-background-more p-5 lg:sticky lg:top-5">
                        <GoogleMap
                            activeMarkerHandler={(uuid) => clickOnMarkerHandler(uuid)}
                            markers={mappedStores}
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
