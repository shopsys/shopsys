import { Checkbox, CheckboxProps } from './Checkbox';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type CheckboxControlledProps = {
    name: string;
    render: (input: JSX.Element, currentValue: any) => ReactElement<any, any> | null;
    checkboxProps: Pick<CheckboxProps, 'count' | 'disabled' | 'label' | 'required' | 'dataTestId' | 'className'>;
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
            <FormLineError dataTestId={checkboxId + '-error'} error={error} inputType="checkbox" />
        </>,
        field.value,
    );
};
