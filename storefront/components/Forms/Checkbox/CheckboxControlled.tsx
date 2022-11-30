import { FormLineError } from '../Lib/FormLineError/FormLineError';
import { Checkbox, CheckboxProps } from './Checkbox';
import { FC, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type CheckboxControlledProps = {
    name: string;
    render: (input: JSX.Element, currentValue: any) => ReactElement<any, any> | null;
    checkboxProps: Pick<CheckboxProps, 'count' | 'disabled' | 'label' | 'required' | 'testIdentifier'>;
    control: Control<any>;
    formName: string;
};

export const CheckboxControlled: FC<CheckboxControlledProps> = ({ name, render, control, formName, checkboxProps }) => {
    const {
        fieldState: { error },
        field,
    } = useController({ name, control });
    const checkboxId = formName + '-' + name;

    return render(
        <>
            <Checkbox {...checkboxProps} {...field} id={checkboxId} />
            <FormLineError error={error} inputType="checkbox" testIdentifier={checkboxId + '-error'} />
        </>,
        field.value,
    );
};
