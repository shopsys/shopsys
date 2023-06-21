import { FormLineError } from '../Lib/FormLineError';
import { Checkbox, CheckboxProps } from './Checkbox';
import { ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type CheckboxControlledProps = {
    name: string;
    render: (input: JSX.Element, currentValue: any) => ReactElement<any, any> | null;
    checkboxProps: Pick<CheckboxProps, 'count' | 'disabled' | 'label' | 'required' | 'dataTestId'>;
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
            <FormLineError error={error} inputType="checkbox" dataTestId={checkboxId + '-error'} />
        </>,
        field.value,
    );
};
