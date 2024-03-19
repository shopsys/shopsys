import { Textarea, TextareaProps } from './Textarea';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { FocusEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type TextareaControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    textareaProps: Pick<TextareaProps, 'disabled' | 'label' | 'required' | 'rows' | 'onBlur'>;
    control: Control<any>;
    formName: string;
};

export const TextareaControlled: FC<TextareaControlledProps> = ({ name, render, control, formName, textareaProps }) => {
    const {
        fieldState: { invalid, error },
        field,
    } = useController({ name, control });
    const textareaId = formName + '-' + name;

    const onBlurHandler: FocusEventHandler<HTMLTextAreaElement> = (event) => {
        field.onBlur();

        if (textareaProps.onBlur) {
            textareaProps.onBlur(event);
        }
    };

    return render(
        <>
            <Textarea {...textareaProps} {...field} hasError={invalid} id={textareaId} onBlur={onBlurHandler} />
            <FormLineError error={error} inputType="textarea" />
        </>,
    );
};
