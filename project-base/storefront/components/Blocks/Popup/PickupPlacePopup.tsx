import { SkeletonModuleTransportStores } from 'components/Blocks/Skeleton/SkeletonModuleTransportStores';
import { STORE_LIST_PAGE_SIZE } from 'components/Blocks/StoreList/constants';
import { mergeStoreConnections } from 'components/Blocks/StoreList/mergeStoreConnections';
import { StoresWrapper } from 'components/Blocks/StoreList/StoresWrapper';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import {
    TransportStoresQueryDocument,
    TypeTransportStoresQuery,
    TypeTransportStoresQueryVariables,
    useTransportStoresQuery,
} from 'graphql/requests/transports/queries/TransportStoresQuery.generated';
import { TypeCoordinates } from 'graphql/types';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { useDebounce } from 'utils/useDebounce';

type PickupPlacePopupProps = {
    transportUuid: string;
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

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({ transportUuid, onChangePickupPlaceCallback }) => {
    const { t } = useTranslation();
    const client = useClient();
    const { pickupPlace } = useCurrentCart();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState(pickupPlace?.identifier ?? '');
    const [selectedPickupPlace, setSelectedPickupPlace] = useState<StoreOrPacketeryPoint | null>(pickupPlace ?? null);
    const [searchTextValue, setSearchTextValue] = useState<string>('');
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const [userCoordinates, setUserCoordinates] = useState<TypeCoordinates | null>(defaultUserCoordinates);
    const [transportStores, setTransportStores] = useState<TypeListedStoreConnectionFragment | null>(null);
    const [isLoadingMoreTransportStores, setIsLoadingMoreTransportStores] = useState(false);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
    const isSearchTextDebouncing = searchTextValue !== debouncedSearchTextValue;
    const isDistanceFromSearchText = debouncedSearchTextValue.trim() !== '';
    const transportStoresQueryVariables = useMemo(
        () => ({
            uuid: transportUuid,
            searchText: debouncedSearchTextValue || null,
            coordinates: userCoordinates,
            first: STORE_LIST_PAGE_SIZE,
            after: null,
        }),
        [debouncedSearchTextValue, transportUuid, userCoordinates],
    );
    const transportStoresQueryKey = JSON.stringify(transportStoresQueryVariables);
    const transportStoresQueryKeyRef = useRef(transportStoresQueryKey);
    const [{ data: transportStoresData, fetching: isFetchingTransportStores }] = useTransportStoresQuery({
        variables: transportStoresQueryVariables,
    });

    const onConfirmPickupPlaceHandler = () => {
        onChangePickupPlaceCallback(transportUuid, selectedPickupPlace);
    };

    useEffect(() => {
        transportStoresQueryKeyRef.current = transportStoresQueryKey;
        setIsLoadingMoreTransportStores(false);
    }, [transportStoresQueryKey]);

    useEffect(() => {
        if (transportStoresData?.transport?.stores) {
            setTransportStores(transportStoresData.transport.stores);
        }
    }, [transportStoresData?.transport?.stores]);

    useEffect(() => {
        if (selectedStoreUuid === '') {
            setSelectedPickupPlace(null);

            return;
        }

        const selectedStore = findStoreByUuid(transportStores, selectedStoreUuid);

        if (selectedStore) {
            setSelectedPickupPlace(selectedStore);
        }
    }, [selectedStoreUuid, transportStores]);

    const onSelectStoreHandler = useCallback(
        (newStoreUuid: string | null) => {
            setSelectedStoreUuid(newStoreUuid ?? '');
            setSelectedPickupPlace(findStoreByUuid(transportStores, newStoreUuid));
        },
        [transportStores],
    );

    const onSearchTextHandler = useCallback((searchText: string) => {
        setSearchTextValue(searchText);
    }, []);

    const onUserCoordinatesHandler = useCallback((coordinates: TypeCoordinates | null) => {
        setUserCoordinates(coordinates);
    }, []);

    const onLoadMoreTransportStoresHandler = useCallback(async () => {
        if (
            transportStores === null ||
            !transportStores.pageInfo.hasNextPage ||
            transportStores.pageInfo.endCursor === null
        ) {
            return;
        }

        if (isFetchingTransportStores || isLoadingMoreTransportStores || isSearchTextDebouncing) {
            return;
        }

        const requestedTransportStoresQueryKey = transportStoresQueryKey;

        setIsLoadingMoreTransportStores(true);

        try {
            const transportStoresResponse = await client
                .query<TypeTransportStoresQuery, TypeTransportStoresQueryVariables>(TransportStoresQueryDocument, {
                    ...transportStoresQueryVariables,
                    after: transportStores.pageInfo.endCursor,
                })
                .toPromise();

            if (
                transportStoresQueryKeyRef.current !== requestedTransportStoresQueryKey ||
                !transportStoresResponse.data?.transport?.stores
            ) {
                return;
            }

            setTransportStores((currentStores) =>
                currentStores === null
                    ? transportStoresResponse.data!.transport!.stores!
                    : mergeStoreConnections(currentStores, transportStoresResponse.data!.transport!.stores!),
            );
        } finally {
            setIsLoadingMoreTransportStores(false);
        }
    }, [
        client,
        isFetchingTransportStores,
        isLoadingMoreTransportStores,
        isSearchTextDebouncing,
        transportStores,
        transportStoresQueryKey,
        transportStoresQueryVariables,
    ]);

    return (
        <Popup
            className="min-h-[min(600px,80dvh)] w-11/12 max-w-6xl md:min-h-auto"
            contentClassName="flex min-h-0 flex-1 flex-col overflow-hidden"
            title={t('Choose the store where you are going to pick up your order')}
        >
            <div id={PICKUP_PLACE_POPUP_STORES_SCROLL_TARGET_ID} className="min-h-0 flex-1 overflow-y-auto pr-1">
                {isFetchingTransportStores && transportStores === null && <SkeletonModuleTransportStores />}

                {transportStores && (
                    <StoresWrapper
                        isDistanceFromSearchText={isDistanceFromSearchText}
                        isFetchingStores={isFetchingTransportStores || isSearchTextDebouncing}
                        isLoadingMoreStores={isLoadingMoreTransportStores}
                        scrollableTargetId={PICKUP_PLACE_POPUP_STORES_SCROLL_TARGET_ID}
                        searchTextValue={searchTextValue}
                        selectedStoreUuid={selectedStoreUuid}
                        stores={transportStores}
                        shouldShowTitle={false}
                        shouldWrapInWebline={false}
                        userCoordinates={userCoordinates}
                        onLoadMoreStoresCallback={onLoadMoreTransportStoresHandler}
                        onSearchTextCallback={onSearchTextHandler}
                        onSelectStoreCallback={onSelectStoreHandler}
                        onUserCoordinatesCallback={onUserCoordinatesHandler}
                    />
                )}
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
                    {t('Confirm')}
                </Button>
            </div>
        </Popup>
    );
};
