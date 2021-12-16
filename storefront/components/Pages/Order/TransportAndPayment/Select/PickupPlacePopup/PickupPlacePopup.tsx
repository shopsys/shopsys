import { PickupPlacePopupWrapperStyled, PopupButtonWrapperStyled } from './PickupPlacePopup.style';
import { usePickupPlaceForm, usePickupPlaceFormMeta } from './formMeta';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { PickupPlaceType } from 'types/pickupPlace';
import Popup from 'components/Layout/Popup';
import StoreSelect from './PlaceSelect/StoreSelect';
import { TransportType } from 'types/transport';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useWatch } from 'react-hook-form';

type PickupPlacePopupProps = {
    isVisible: boolean;
    transport: TransportType;
    onChangePickupPlaceCallback: (selectedPickupPlace: PickupPlaceType | null) => void;
    onClosePickupPlacePopupCallback: () => void;
};

const PickupPlacePopup: FC<PickupPlacePopupProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [formProviderMethods] = usePickupPlaceForm();
    const formMeta = usePickupPlaceFormMeta(formProviderMethods);
    const pickupPlaceValue = useWatch({ name: formMeta.fields.pickupPlace.name, control: formProviderMethods.control });

    const onConfirmPickupPlaceHandler = () => {
        const selectedPickupPlace = props.transport.stores.find((store) => store.identifier === pickupPlaceValue);

        props.onChangePickupPlaceCallback(selectedPickupPlace === undefined ? null : selectedPickupPlace);

        formProviderMethods.setValue(formMeta.fields.pickupPlace.name, '');
    };

    const onClosePickupPlacePopupHandler = () => {
        props.onClosePickupPlacePopupCallback();
        formProviderMethods.setValue(formMeta.fields.pickupPlace.name, '');
    };

    return (
        <Popup
            isVisible={props.isVisible}
            onCloseCallback={onClosePickupPlacePopupHandler}
            wrapperComponent={PickupPlacePopupWrapperStyled}
        >
            <Heading type="h2">{formMeta.fields.pickupPlace.label}</Heading>
            <StoreSelect
                control={formProviderMethods.control}
                transport={props.transport}
                pickupPlaceValue={pickupPlaceValue}
            />
            <PopupButtonWrapperStyled>
                <Button type="button" onClick={onClosePickupPlacePopupHandler}>
                    {t('Close')}
                </Button>
                <Button type="button" isDisabled={pickupPlaceValue === ''} onClick={onConfirmPickupPlaceHandler}>
                    {t('Confirm')}
                </Button>
            </PopupButtonWrapperStyled>
        </Popup>
    );
};

export default PickupPlacePopup;
