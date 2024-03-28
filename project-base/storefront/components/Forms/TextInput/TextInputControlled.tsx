import { TextInput, TextInputProps } from './TextInput';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, FocusEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type TextInputControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    textInputProps: Pick<
        TextInputProps,
        'disabled' | 'required' | 'onBlur' | 'onKeyDown' | 'onChange' | 'type' | 'label' | 'inputSize' | 'autoComplete'
    >;
    control: Control<any>;
    formName: string;
};

export const TextInputControlled: FC<TextInputControlledProps> = ({
    name,
    render,
    control,
    textInputProps,
    formName,
}) => {
    const {
        fieldState: { invalid, error },
        field,
    } = useController({ name, control });
    const textInputId = formName + '-' + name;

    const onBlurHandler: FocusEventHandler<HTMLInputElement> = (event) => {
        field.onBlur();

        if (textInputProps.onBlur) {
            textInputProps.onBlur(event);
        }
    };

    const onChangeHandler: ChangeEventHandler<HTMLInputElement> = (event) => {
        field.onChange(event);

        if (textInputProps.onChange) {
            textInputProps.onChange(event);
        }
    };

    return render(
        <>
            <TextInput
                {...textInputProps}
                {...field}
                hasError={invalid}
                id={textInputId}
                onBlur={onBlurHandler}
                onChange={onChangeHandler}
            />
            <FormLineError error={error} inputType="text-input" textInputSize={textInputProps.inputSize} />
        </>,
    );
};
