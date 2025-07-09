import { SkeletonModuleTransportStores } from 'components/Blocks/Skeleton/SkeletonModuleTransportStores';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { StoreSelect } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/StoreSelect';
import { TIDs } from 'cypress/tids';
import { useTransportStoresQuery } from 'graphql/requests/transports/queries/TransportStoresQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type PickupPlacePopupProps = {
    transportUuid: string;
    onChangePickupPlaceCallback: (transportUuid: string, selectedPickupPlace: StoreOrPacketeryPoint | null) => void;
};

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({ transportUuid, onChangePickupPlaceCallback }) => {
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState(pickupPlace?.identifier ?? '');
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const [{ data: transportStoresData, fetching: isFetchingTransportStores }] = useTransportStoresQuery({
        variables: { uuid: transportUuid },
    });

    const onConfirmPickupPlaceHandler = () => {
        const selectedPickupPlace = transportStoresData?.transport?.stores?.edges?.find(
            (storeEdge) => storeEdge?.node?.identifier === selectedStoreUuid,
        )?.node;

        onChangePickupPlaceCallback(transportUuid, selectedPickupPlace === undefined ? null : selectedPickupPlace);
    };

    const onSelectStoreHandler = (newStoreUuid: string | null) => {
        setSelectedStoreUuid(newStoreUuid ?? '');
    };

    return (
        <Popup
            className="min-h-[min(600px,80dvh)] w-11/12 max-w-4xl md:min-h-auto"
            contentClassName="overflow-y-auto flex flex-col flex-1"
            title={t('Choose the store where you are going to pick up your order')}
        >
            {isFetchingTransportStores && <SkeletonModuleTransportStores />}

            {transportStoresData?.transport?.stores && (
                <StoreSelect
                    selectedStoreUuid={selectedStoreUuid}
                    stores={transportStoresData.transport.stores}
                    onSelectStoreCallback={onSelectStoreHandler}
                />
            )}

            <div className="bg-background-default sticky -inset-4 mt-auto flex justify-between p-4">
                <Button onClick={() => updatePortalContent(null)}>{t('Close')}</Button>

                <Button
                    isDisabled={selectedStoreUuid === ''}
                    tid={TIDs.pages_order_pickupplace_popup_confirm}
                    onClick={onConfirmPickupPlaceHandler}
                >
                    {t('Confirm')}
                </Button>
            </div>
        </Popup>
    );
};
