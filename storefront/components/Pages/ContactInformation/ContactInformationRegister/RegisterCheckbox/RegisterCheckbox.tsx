import { FC, useEffect } from 'react';
import Checkbox from 'components/Forms/Checkbox';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type RegisterCheckboxProps = {
    field: any;
    setIsRegisterChecked: (p: boolean) => void;
};

const RegisterCheckbox: FC<RegisterCheckboxProps> = (props) => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        props.setIsRegisterChecked(props.field.value);
    }, [props.field.value]);

    return (
        <>
            <Checkbox
                id="newsletter_form-register"
                name={props.field.name}
                label={t('I want to register with an order')}
                fieldRef={props.field}
            />
        </>
    );
};

/* @component */
export default RegisterCheckbox;
