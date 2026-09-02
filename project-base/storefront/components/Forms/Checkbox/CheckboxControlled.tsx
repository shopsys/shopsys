import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, ReactElement } from 'react';
import { Control, FieldPath, FieldValues, useController } from 'react-hook-form';
import { FunctionComponentProps } from 'types/globals';
import { Checkbox, CheckboxProps } from './Checkbox';

type CheckboxControlledProps<TFieldValues extends FieldValues, TTransformedValues> = {
    name: FieldPath<TFieldValues>;
    render?: (input: ReactElement) => ReactElement<any, any> | null;
    checkboxProps: Pick<
        CheckboxProps,
        'count' | 'disabled' | 'label' | 'required' | 'className' | 'labelWrapperClassName'
    >;
    control: Control<TFieldValues, any, TTransformedValues>;
    formName: string;
    onChange?: ChangeEventHandler<HTMLInputElement>;
};

export const CheckboxControlled = <TFieldValues extends FieldValues, TTransformedValues = TFieldValues>({
    name,
    render,
    control,
    formName,
    checkboxProps,
    onChange,
}: CheckboxControlledProps<TFieldValues, TTransformedValues> & FunctionComponentProps) => {
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
