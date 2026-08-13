import { StoresWrapper } from 'components/Blocks/StoreList/StoresWrapper';
import { usePaginatedStoreConnection } from 'components/Blocks/StoreList/usePaginatedStoreConnection';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import {
    TransportStoresQueryDocument,
    TypeTransportStoresQuery,
} from 'graphql/requests/transports/queries/TransportStoresQuery.generated';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type PickupPlacePopupProps = {
    transportUuid: string;
    lastOrderPickupPlace: StoreOrPacketeryPoint | null;
    onChangePickupPlaceCallback: (transportUuid: string, selectedPickupPlace: StoreOrPacketeryPoint | null) => void;
};

const PICKUP_PLACE_POPUP_STORES_SCROLL_TARGET_ID = 'pickup-place-popup-stores-scroll';

const findStoreByUuid = (
    stores: TypeListedStoreConnectionFragment | null,
    storeUuid: string | null,
): StoreOrPacketeryPoint | null => {
    if (storeUuid === null) {
        return null;
    }

    return stores?.edges?.find((storeEdge) => storeEdge?.node?.identifier === storeUuid)?.node ?? null;
};

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({
    transportUuid,
    lastOrderPickupPlace,
    onChangePickupPlaceCallback,
}) => {
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const [selectedStoreUuid, setSelectedStoreUuid] = useState(pickupPlace?.identifier ?? '');
    const [selectedPickupPlace, setSelectedPickupPlace] = useState<StoreOrPacketeryPoint | null>(pickupPlace ?? null);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const transportStoresAdditionalQueryVariables = useMemo(
        () => ({ uuid: transportUuid, cartUuid }),
        [transportUuid, cartUuid],
    );
    const getStoreConnectionFromData = useCallback(
        (data: TypeTransportStoresQuery | undefined) => data?.transport?.stores,
        [],
    );
    const {
        appliedSearchTextValue,
        isDistanceFromSearchText,
        isFetchingStores,
        isLoadingMoreStores,
        loadMoreStores,
        searchTextValue,
        setSearchTextValue,
        setUserCoordinates,
        storeConnectionError,
        stores: transportStores,
        userCoordinates,
    } = usePaginatedStoreConnection<TypeTransportStoresQuery, { uuid: string; cartUuid: string | null }>({
        queryDocument: TransportStoresQueryDocument,
        additionalQueryVariables: transportStoresAdditionalQueryVariables,
        getStoreConnectionFromData,
        // the per-store expected delivery dates change with the cart contents and with time, but the query
        // variables (the cache key) stay the same — a cached response could therefore promise outdated dates
        requestPolicy: 'network-only',
    });
    const storeConnectionErrorMessage = t('Stores could not be loaded. Please try again later.');
    const findSelectableStoreByUuid = useCallback(
        (storeUuid: string | null) =>
            findStoreByUuid(transportStores, storeUuid) ??
            (lastOrderPickupPlace?.identifier === storeUuid ? lastOrderPickupPlace : null),
        [lastOrderPickupPlace, transportStores],
    );

    const onConfirmPickupPlaceHandler = () => {
        onChangePickupPlaceCallback(transportUuid, selectedPickupPlace);
    };

    useEffect(() => {
        if (selectedStoreUuid === '') {
            setSelectedPickupPlace(null);

            return;
        }

        const selectedStore = findSelectableStoreByUuid(selectedStoreUuid);

        if (selectedStore) {
            setSelectedPickupPlace(selectedStore);
        }
    }, [findSelectableStoreByUuid, selectedStoreUuid]);

    const onSelectStoreHandler = useCallback(
        (newStoreUuid: string | null) => {
            setSelectedStoreUuid(newStoreUuid ?? '');
            setSelectedPickupPlace(findSelectableStoreByUuid(newStoreUuid));
        },
        [findSelectableStoreByUuid],
    );

    return (
        <Popup
            className="h-[min(760px,80dvh)] max-h-[80dvh] w-11/12 max-w-6xl"
            contentClassName="flex min-h-0 flex-1 flex-col overflow-hidden"
            title={t('Choose the store where you are going to pick up your order')}
        >
            <div className="min-h-0 flex-1 overflow-y-auto pr-1" id={PICKUP_PLACE_POPUP_STORES_SCROLL_TARGET_ID}>
                <StoresWrapper
                    appliedSearchTextValue={appliedSearchTextValue}
                    isDistanceFromSearchText={isDistanceFromSearchText}
                    isFetchingStores={isFetchingStores}
                    isLoadingMoreStores={isLoadingMoreStores}
                    priorityStore={searchTextValue === '' ? lastOrderPickupPlace : null}
                    scrollableTargetId={PICKUP_PLACE_POPUP_STORES_SCROLL_TARGET_ID}
                    searchTextValue={searchTextValue}
                    selectedStoreUuid={selectedStoreUuid}
                    storeConnectionErrorMessage={storeConnectionError ? storeConnectionErrorMessage : undefined}
                    stores={transportStores}
                    userCoordinates={userCoordinates}
                    variant="pickupSelection"
                    onLoadMoreStoresCallback={loadMoreStores}
                    onSearchTextCallback={setSearchTextValue}
                    onSelectStoreCallback={onSelectStoreHandler}
                    onUserCoordinatesCallback={setUserCoordinates}
                />
            </div>

            <div className="mt-3 flex shrink-0 justify-between border-border-less border-t bg-background-default pt-3">
                <Button variant="inverted" onClick={closePortalContent}>
                    {t('Close')}
                </Button>

                <Button
                    disabled={selectedPickupPlace === null}
                    hasDisabledLook={selectedPickupPlace === null}
                    tid={TIDs.pages_order_pickupplace_popup_confirm}
                    onClick={onConfirmPickupPlaceHandler}
                >
                    {t('Select store')}
                </Button>
            </div>
        </Popup>
    );
};
