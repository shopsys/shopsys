import { PickupPlacePopupWrapperStyled, PopupButtonWrapperStyled } from './PickupPlacePopup.style';
import { useForm, useWatch } from 'react-hook-form';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { PickupPlaceType } from 'connectors/transports/pickupPlace/types';
import Popup from 'components/Layout/Popup';
import StoreSelect from './PlaceSelect/StoreSelect';
import { TransportType } from 'connectors/transports/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type PickupPlacePopupProps = {
    isVisible: boolean;
    transport: TransportType;
    onChangePickupPlaceCallback: (selectedPickupPlace: PickupPlaceType | null) => void;
    onClosePickupPlacePopupCallback: () => void;
};

const PickupPlacePopup: FC<PickupPlacePopupProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useForm({ defaultValues: { pickupPlace: null } });
    const pickupPlaceValue = useWatch({ name: 'pickupPlace', control: formProviderMethods.control });

    const onConfirmPickupPlaceHandler = () => {
        const selectedPickupPlace = props.transport.stores.find((store) => store.identifier === pickupPlaceValue);

        props.onChangePickupPlaceCallback(selectedPickupPlace === undefined ? null : selectedPickupPlace);
        formProviderMethods.setValue('pickupPlace', null);
    };

    const onClosePickupPlacePopupHandler = () => {
        props.onClosePickupPlacePopupCallback();
        formProviderMethods.setValue('pickupPlace', null);
    };

    return (
        <Popup
            isVisible={props.isVisible}
            onCloseCallback={onClosePickupPlacePopupHandler}
            wrapperComponent={PickupPlacePopupWrapperStyled}
        >
            <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
            <StoreSelect
                control={formProviderMethods.control}
                transport={props.transport}
                pickupPlaceValue={pickupPlaceValue}
            />
            <PopupButtonWrapperStyled>
                <Button type="button" onClick={onClosePickupPlacePopupHandler}>
                    {t('Close')}
                </Button>
                <Button type="button" isDisabled={pickupPlaceValue === null} onClick={onConfirmPickupPlaceHandler}>
                    {t('Confirm')}
                </Button>
            </PopupButtonWrapperStyled>
        </Popup>
    );
};

export default PickupPlacePopup;
