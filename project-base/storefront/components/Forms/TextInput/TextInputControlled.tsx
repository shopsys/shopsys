import { FormLine, FormLineWidth } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, FocusEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';
import { TextInput, TextInputProps } from './TextInput';

type TextInputControlledProps = {
    name: string;
    render?: (input: ReactElement) => ReactElement<any, any> | null;
    width?: FormLineWidth;
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
        | 'aria-describedby'
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
    width,
    control,
    textInputProps,
    formName,
    isWithoutFormLineError,
}) => {
    const {
        fieldState: { error },
        field,
    } = useController({ name, control });
    const textInputId = `${formName}-${name}`;
    const errorId = `${textInputId}-error`;
    const describedBy = [textInputProps['aria-describedby'], error ? errorId : undefined].filter(Boolean).join(' ');

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

    const element = (
        <>
            <TextInput
                {...textInputProps}
                {...field}
                aria-describedby={describedBy || undefined}
                aria-invalid={error ? true : undefined}
                hasError={!!error}
                hasWarning={textInputProps.hasWarning}
                id={textInputId}
                onBlur={onBlurHandler}
                onChange={onChangeHandler}
            />
            {!isWithoutFormLineError && (
                <FormLineError
                    error={error}
                    id={errorId}
                    inputType="text-input"
                    textInputSize={textInputProps.inputSize}
                />
            )}
        </>
    );

    if (render) {
        return render(element);
    }

    return <FormLine width={width}>{element}</FormLine>;
};
