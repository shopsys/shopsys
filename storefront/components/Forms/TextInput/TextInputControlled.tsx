import { FormLineError } from '../Lib/FormLineError/FormLineError';
import { TextInput, TextInputProps } from './TextInput';
import { FC, FocusEventHandler, ReactElement, useCallback } from 'react';
import { Control, useController } from 'react-hook-form';

type TextInputControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    textInputProps: Pick<
        TextInputProps,
        | 'disabled'
        | 'required'
        | 'onBlur'
        | 'onKeyPress'
        | 'className'
        | 'type'
        | 'label'
        | 'testIdentifier'
        | 'inputSize'
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

    const combinedOnBlurHandler: FocusEventHandler<HTMLInputElement> = useCallback(
        (event) => {
            field.onBlur();

            if (textInputProps.onBlur !== undefined) {
                textInputProps.onBlur(event);
            }
        },
        [field, textInputProps],
    );

    return render(
        <>
            <TextInput
                {...textInputProps}
                {...field}
                id={textInputId}
                hasError={invalid}
                onBlur={combinedOnBlurHandler}
            />
            <FormLineError
                error={error}
                textInputSize={textInputProps.inputSize}
                inputType="text-input"
                testIdentifier={`${textInputId}-error`}
            />
        </>,
    );
};
