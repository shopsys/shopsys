import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';
import { Checkbox, CheckboxProps } from './Checkbox';

type CheckboxControlledProps = {
    name: string;
    render?: (input: ReactElement) => ReactElement<any, any> | null;
    checkboxProps: Pick<CheckboxProps, 'count' | 'disabled' | 'label' | 'required' | 'className'>;
    control: Control<any>;
    formName: string;
    onChange?: ChangeEventHandler<HTMLInputElement>;
};

export const CheckboxControlled: FC<CheckboxControlledProps> = ({
    name,
    render,
    control,
    formName,
    checkboxProps,
    onChange,
}) => {
    const {
        fieldState: { error },
        field,
    } = useController({ name, control });
    const checkboxId = `${formName}-${name}`;

    const onChangeHandler: ChangeEventHandler<HTMLInputElement> = (event) => {
        field.onChange(event);

        if (onChange) {
            onChange(event);
        }
    };

    const element = (
        <>
            <Checkbox {...checkboxProps} {...field} id={checkboxId} onChange={onChangeHandler} />
            <FormLineError error={error} inputType="checkbox" />
        </>
    );

    if (render) {
        return render(element);
    }

    return <ChoiceFormLine>{element}</ChoiceFormLine>;
};
