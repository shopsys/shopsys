import { StoreSelect } from './StoreSelect';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useState } from 'react';
import { ListedStoreFragmentApi } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';

type PickupPlacePopupProps = {
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi;
    onChangePickupPlaceCallback: (selectedPickupPlace: ListedStoreFragmentApi | null) => void;
    onClosePickupPlacePopupCallback: () => void;
};

const TEST_IDENTIFIER = 'pages-order-pickupplace-popup-';

export const PickupPlacePopup: FC<PickupPlacePopupProps> = ({
    transport,
    onChangePickupPlaceCallback,
    onClosePickupPlacePopupCallback,
}) => {
    const t = useTypedTranslationFunction();
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
        <Popup onCloseCallback={onClosePickupPlacePopupHandler} className="w-11/12 max-w-4xl">
            <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
            <StoreSelect
                transport={transport}
                selectedStoreUuid={selectedStoreUuid}
                onSelectStoreCallback={onSelectStoreHandler}
            />
            <div className="mt-5 flex justify-between">
                <Button onClick={onClosePickupPlacePopupHandler} dataTestId={TEST_IDENTIFIER + 'close'}>
                    {t('Close')}
                </Button>
                <Button
                    isDisabled={selectedStoreUuid === ''}
                    onClick={onConfirmPickupPlaceHandler}
                    dataTestId={TEST_IDENTIFIER + 'confirm'}
                >
                    {t('Confirm')}
                </Button>
            </div>
        </Popup>
    );
};
