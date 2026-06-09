import { SkeletonModuleTransportStores } from 'components/Blocks/Skeleton/SkeletonModuleTransportStores';
import { StoresWrapper } from 'components/Blocks/StoreList/StoresWrapper';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { useTransportStoresQuery } from 'graphql/requests/transports/queries/TransportStoresQuery.generated';
import { TypeCoordinates } from 'graphql/types';
import { useCallback, useEffect, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { useDebounce } from 'utils/useDebounce';

type PickupPlacePopupProps = {
    transportUuid: string;
    onChangePickupPlaceCallback: (transportUuid: string, selectedPickupPlace: StoreOrPacketeryPoint | null) => void;
};

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({ transportUuid, onChangePickupPlaceCallback }) => {
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState(pickupPlace?.identifier ?? '');
    const [selectedPickupPlace, setSelectedPickupPlace] = useState<StoreOrPacketeryPoint | null>(pickupPlace ?? null);
    const [searchTextValue, setSearchTextValue] = useState<string>('');
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const [userCoordinates, setUserCoordinates] = useState<TypeCoordinates | null>(defaultUserCoordinates);
    const [transportStores, setTransportStores] = useState<TypeListedStoreConnectionFragment | null>(null);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
    const [{ data: transportStoresData, fetching: isFetchingTransportStores }] = useTransportStoresQuery({
        variables: { uuid: transportUuid, searchText: debouncedSearchTextValue || null, coordinates: userCoordinates },
    });

    const onConfirmPickupPlaceHandler = () => {
        onChangePickupPlaceCallback(transportUuid, selectedPickupPlace);
    };

    useEffect(() => {
        if (transportStoresData?.transport?.stores) {
            setTransportStores(transportStoresData.transport.stores);
        }
    }, [transportStoresData?.transport?.stores]);

    const onSelectStoreHandler = useCallback(
        (newStoreUuid: string | null) => {
            setSelectedStoreUuid(newStoreUuid ?? '');
            const selectedStore = transportStoresData?.transport?.stores?.edges?.find(
                (storeEdge) => storeEdge?.node?.identifier === newStoreUuid,
            )?.node;
            setSelectedPickupPlace(selectedStore ?? null);
        },
        [transportStoresData?.transport?.stores?.edges],
    );

    const onSearchTextHandler = useCallback((searchText: string) => {
        setSearchTextValue(searchText);
    }, []);

    const onUserCoordinatesHandler = useCallback((coordinates: TypeCoordinates | null) => {
        setUserCoordinates(coordinates);
    }, []);

    return (
        <Popup
            className="min-h-[min(600px,80dvh)] w-11/12 max-w-6xl md:min-h-auto"
            contentClassName="overflow-y-auto flex flex-col flex-1"
            title={t('Choose the store where you are going to pick up your order')}
        >
            {isFetchingTransportStores && transportStores === null && <SkeletonModuleTransportStores />}

            {transportStores && (
                <StoresWrapper
                    isFetchingStores={isFetchingTransportStores}
                    searchTextValue={searchTextValue}
                    selectedStoreUuid={selectedStoreUuid}
                    stores={transportStores}
                    shouldShowTitle={false}
                    shouldWrapInWebline={false}
                    userCoordinates={userCoordinates}
                    onSearchTextCallback={onSearchTextHandler}
                    onSelectStoreCallback={onSelectStoreHandler}
                    onUserCoordinatesCallback={onUserCoordinatesHandler}
                />
            )}

            <div className="sticky -inset-4 mt-auto flex justify-between bg-background-default pt-3">
                <Button variant="inverted" onClick={closePortalContent}>
                    {t('Close')}
                </Button>

                <Button
                    hasDisabledLook={selectedStoreUuid === ''}
                    tid={TIDs.pages_order_pickupplace_popup_confirm}
                    onClick={onConfirmPickupPlaceHandler}
                >
                    {t('Confirm')}
                </Button>
            </div>
        </Popup>
    );
};
