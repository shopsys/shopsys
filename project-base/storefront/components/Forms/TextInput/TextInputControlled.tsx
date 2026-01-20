import { TextInput, TextInputProps } from './TextInput';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, FocusEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type TextInputControlledProps = {
    name: string;
    render: (input: ReactElement) => ReactElement<any, any> | null;
    textInputProps: Pick<
        TextInputProps,
        | 'disabled'
        | 'required'
        | 'onBlur'
        | 'onKeyDown'
        | 'onChange'
        | 'type'
        | 'label'
        | 'inputSize'
        | 'autoComplete'
        | 'className'
        | 'inputMode'
        | 'aria-label'
        | 'aria-labelledby'
        | 'hasWarning'
    >;
    control: Control<any>;
    formName: string;
    isWithoutFormLineError?: boolean;
};

export const TextInputControlled: FC<TextInputControlledProps> = ({
    name,
    render,
    control,
    textInputProps,
    formName,
    isWithoutFormLineError,
}) => {
    const {
        fieldState: { error },
        field,
    } = useController({ name, control });
    const textInputId = formName + '-' + name;

    const onBlurHandler: FocusEventHandler<HTMLInputElement> = (event) => {
        field.onBlur();

        if (textInputProps.onBlur) {
            textInputProps.onBlur(event);
        }

        window.getSelection()?.removeAllRanges();
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
                hasError={!!error}
                hasWarning={textInputProps.hasWarning}
                id={textInputId}
                onBlur={onBlurHandler}
                onChange={onChangeHandler}
            />
            {!isWithoutFormLineError && (
                <FormLineError error={error} inputType="text-input" textInputSize={textInputProps.inputSize} />
            )}
        </>,
    );
};
