import { StoreSelect } from './StoreSelect/StoreSelect';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useState } from 'react';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

type PickupPlacePopupProps = {
    isVisible: boolean;
    transport: TransportType;
    onChangePickupPlaceCallback: (selectedPickupPlace: PickupPlaceType | null) => void;
    onClosePickupPlacePopupCallback: () => void;
};

const TEST_IDENTIFIER = 'pages-order-pickupplace-popup-';

export const PickupPlacePopup: FC<PickupPlacePopupProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [selectedStoreUuid, setSelectedStoreUuid] = useState('');

    const onConfirmPickupPlaceHandler = () => {
        const selectedPickupPlace = props.transport.stores.find((store) => store.identifier === selectedStoreUuid);

        props.onChangePickupPlaceCallback(selectedPickupPlace === undefined ? null : selectedPickupPlace);
    };

    const onClosePickupPlacePopupHandler = () => {
        props.onClosePickupPlacePopupCallback();
    };

    const onSelectStoreHandler = (newStoreUuid: string | null) => {
        setSelectedStoreUuid(newStoreUuid ?? '');
    };

    return (
        <Popup
            isVisible={props.isVisible}
            onCloseCallback={onClosePickupPlacePopupHandler}
            wrapperComponent={PopupStyled}
            className="w-11/12 max-w-4xl"
        >
            <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
            <StoreSelect
                transport={props.transport}
                selectedStoreUuid={selectedStoreUuid}
                onSelectStoreCallback={onSelectStoreHandler}
            />
            <div className="mt-5 flex justify-between">
                <Button
                    type="button"
                    onClick={onClosePickupPlacePopupHandler}
                    testIdentifier={TEST_IDENTIFIER + 'close'}
                >
                    {t('Close')}
                </Button>
                <Button
                    type="button"
                    isDisabled={selectedStoreUuid === ''}
                    onClick={onConfirmPickupPlaceHandler}
                    testIdentifier={TEST_IDENTIFIER + 'confirm'}
                >
                    {t('Confirm')}
                </Button>
            </div>
        </Popup>
    );
};
