import { StoreSelect } from './StoreSelect';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';

type PickupPlacePopupProps = {
    transport: TypeTransportWithAvailablePaymentsAndStoresFragment;
    onChangePickupPlaceCallback: (
        transport: TypeTransportWithAvailablePaymentsAndStoresFragment,
        selectedPickupPlace: TypeListedStoreFragment | null,
    ) => void;
};

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({ transport, onChangePickupPlaceCallback }) => {
    const { t } = useTranslation();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState('');
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onConfirmPickupPlaceHandler = () => {
        const selectedPickupPlace = transport.stores?.edges?.find(
            (storeEdge) => storeEdge?.node?.identifier === selectedStoreUuid,
        )?.node;

        onChangePickupPlaceCallback(transport, selectedPickupPlace === undefined ? null : selectedPickupPlace);
    };

    const onSelectStoreHandler = (newStoreUuid: string | null) => {
        setSelectedStoreUuid(newStoreUuid ?? '');
    };

    return (
        <Popup className="w-11/12 max-w-4xl" contentClassName="overflow-y-auto">
            <div className="h2 mb-3">{t('Choose the store where you are going to pick up your order')}</div>
            <StoreSelect
                selectedStoreUuid={selectedStoreUuid}
                transport={transport}
                onSelectStoreCallback={onSelectStoreHandler}
            />
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
