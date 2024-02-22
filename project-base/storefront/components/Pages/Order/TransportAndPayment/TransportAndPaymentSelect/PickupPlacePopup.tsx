import { StoreSelect } from './StoreSelect';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { ListedStoreFragmentApi, TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

type PickupPlacePopupProps = {
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi;
    onChangePickupPlaceCallback: (selectedPickupPlace: ListedStoreFragmentApi | null) => void;
    onClosePickupPlacePopupCallback: () => void;
};

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({
    transport,
    onChangePickupPlaceCallback,
    onClosePickupPlacePopupCallback,
}) => {
    const { t } = useTranslation();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState('');

    const onConfirmPickupPlaceHandler = () => {
        const selectedPickupPlace = transport.stores?.edges?.find(
            (storeEdge) => storeEdge?.node?.identifier === selectedStoreUuid,
        )?.node;

        onChangePickupPlaceCallback(selectedPickupPlace === undefined ? null : selectedPickupPlace);
    };

    const onClosePickupPlacePopupHandler = () => {
        onClosePickupPlacePopupCallback();
    };

    const onSelectStoreHandler = (newStoreUuid: string | null) => {
        setSelectedStoreUuid(newStoreUuid ?? '');
    };

    return (
        <Popup
            className="w-11/12 max-w-4xl"
            contentClassName="overflow-y-auto"
            onCloseCallback={onClosePickupPlacePopupHandler}
        >
            <div className="h2 mb-3">{t('Choose the store where you are going to pick up your order')}</div>
            <StoreSelect
                selectedStoreUuid={selectedStoreUuid}
                transport={transport}
                onSelectStoreCallback={onSelectStoreHandler}
            />
            <div className="mt-5 flex justify-between">
                <Button onClick={onClosePickupPlacePopupHandler}>{t('Close')}</Button>
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
