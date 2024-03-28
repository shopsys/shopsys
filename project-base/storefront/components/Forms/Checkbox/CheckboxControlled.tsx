import { Checkbox, CheckboxProps } from './Checkbox';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type CheckboxControlledProps = {
    name: string;
    render: (input: JSX.Element, currentValue: any) => ReactElement<any, any> | null;
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
    const checkboxId = formName + '-' + name;

    const onChangeHandler: ChangeEventHandler<HTMLInputElement> = (event) => {
        field.onChange(event);

        if (onChange) {
            onChange(event);
        }
    };

    return render(
        <>
            <Checkbox {...checkboxProps} {...field} id={checkboxId} onChange={onChangeHandler} />
            <FormLineError error={error} inputType="checkbox" />
        </>,
        field.value,
    );
};
