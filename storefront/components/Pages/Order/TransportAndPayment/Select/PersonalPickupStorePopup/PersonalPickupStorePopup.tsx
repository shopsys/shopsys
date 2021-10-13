import { PersonalPickupPopupWrapperStyled, PopupButtonWrapperStyled } from './PersonalPickupStorePopup.style';
import { StoreType, TransportType } from 'connectors/transports/types';
import { useForm, useWatch } from 'react-hook-form';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import Popup from 'components/Layout/Popup';
import StoreSelect from './StoreSelect/StoreSelect';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type PersonalPickupStorePopupProps = {
    isVisible: boolean;
    transport: TransportType;
    onChangePersonalPickupStoreCallback: (selectedPersonalPickupStore: StoreType | null) => void;
};

const PersonalPickupStorePopup: FC<PersonalPickupStorePopupProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useForm({ defaultValues: { personalPickupStore: null } });
    const personalPickupStoreValue = useWatch({ name: 'personalPickupStore', control: formProviderMethods.control });

    const onConfirmPersonalPickupStoreHandler = () => {
        const selectedPersonalPickupStore = props.transport.stores.find(
            (store) => store.uuid === personalPickupStoreValue,
        );

        props.onChangePersonalPickupStoreCallback(
            selectedPersonalPickupStore === undefined ? null : selectedPersonalPickupStore,
        );
        formProviderMethods.setValue('personalPickupStore', null);
    };

    const onClosePersonalPickupStorePopupHandler = () => {
        props.onChangePersonalPickupStoreCallback(null);
        formProviderMethods.setValue('personalPickupStore', null);
    };

    return (
        <Popup
            isVisible={props.isVisible}
            onCloseCallback={onClosePersonalPickupStorePopupHandler}
            wrapperComponent={PersonalPickupPopupWrapperStyled}
        >
            <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
            <StoreSelect
                control={formProviderMethods.control}
                transport={props.transport}
                personalPickupStoreValue={personalPickupStoreValue}
            />
            <PopupButtonWrapperStyled>
                <Button type="button" onClick={onClosePersonalPickupStorePopupHandler}>
                    {t('Close')}
                </Button>
                <Button
                    type="button"
                    isDisabled={personalPickupStoreValue === null}
                    onClick={onConfirmPersonalPickupStoreHandler}
                >
                    {t('Confirm')}
                </Button>
            </PopupButtonWrapperStyled>
        </Popup>
    );
};

export default PersonalPickupStorePopup;
