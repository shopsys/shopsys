import { PickupPlacePopupWrapperStyled, PopupButtonWrapperStyled } from './PickupPlacePopup.style';
import { StoreSelect } from './StoreSelect/StoreSelect';
import Heading from 'components/Basic/Heading';
import Button from 'components/Forms/Button';
import Popup from 'components/Layout/Popup';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
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
            wrapperComponent={PickupPlacePopupWrapperStyled}
        >
            <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
            <StoreSelect
                transport={props.transport}
                selectedStoreUuid={selectedStoreUuid}
                onSelectStoreCallback={onSelectStoreHandler}
            />
            <PopupButtonWrapperStyled>
                <Button type="button" onClick={onClosePickupPlacePopupHandler} data-testid={TEST_IDENTIFIER + 'close'}>
                    {t('Close')}
                </Button>
                <Button
                    type="button"
                    isDisabled={selectedStoreUuid === ''}
                    onClick={onConfirmPickupPlaceHandler}
                    data-testid={TEST_IDENTIFIER + 'confirm'}
                >
                    {t('Confirm')}
                </Button>
            </PopupButtonWrapperStyled>
        </Popup>
    );
};
