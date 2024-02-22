import { TextInput, TextInputProps } from './TextInput';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { FocusEventHandler, ReactElement, useCallback } from 'react';
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

    const combinedOnBlurHandler: FocusEventHandler<HTMLInputElement> = useCallback(
        (event) => {
            field.onBlur();

            if (textInputProps.onBlur) {
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
                hasError={invalid}
                id={textInputId}
                onBlur={combinedOnBlurHandler}
            />
            <FormLineError error={error} inputType="text-input" textInputSize={textInputProps.inputSize} />
        </>,
    );
};
