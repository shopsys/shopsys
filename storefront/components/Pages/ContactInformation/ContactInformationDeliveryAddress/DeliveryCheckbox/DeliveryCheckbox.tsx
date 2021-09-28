import { FC, useEffect } from 'react';
import Checkbox from 'components/Forms/Checkbox';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type DeliveryCheckboxProps = {
    field: any;
    setIsDeliveryAddressChecked: (p: boolean) => void;
};

const DeliveryCheckbox: FC<DeliveryCheckboxProps> = (props) => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        props.setIsDeliveryAddressChecked(props.field.value);
    }, [props.field.value]);

    return (
        <>
            <Checkbox
                id="newsletter_form-deliveryAddress"
                name={props.field.name}
                label={t('Enter the delivery address')}
                fieldRef={props.field}
            />
        </>
    );
};

/* @component */
export default DeliveryCheckbox;
