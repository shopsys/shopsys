import { SkeletonModuleTransportStores } from 'components/Blocks/Skeleton/SkeletonModuleTransportStores';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { StoreSelect } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/StoreSelect';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { useTransportStoresQuery } from 'graphql/requests/transports/queries/TransportStoresQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';

type PickupPlacePopupProps = {
    transportUuid: string;
    onChangePickupPlaceCallback: (transportUuid: string, selectedPickupPlace: TypeListedStoreFragment | null) => void;
};

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({ transportUuid, onChangePickupPlaceCallback }) => {
    const { t } = useTranslation();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState('');
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
        <Popup className="w-11/12 max-w-4xl" contentClassName="overflow-y-auto">
            <div className="h2 mb-3">{t('Choose the store where you are going to pick up your order')}</div>
            {isFetchingTransportStores && <SkeletonModuleTransportStores />}
            {transportStoresData?.transport?.stores && (
                <StoreSelect
                    selectedStoreUuid={selectedStoreUuid}
                    stores={transportStoresData.transport.stores}
                    onSelectStoreCallback={onSelectStoreHandler}
                />
            )}
            <div className="mt-5 flex justify-between">
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
