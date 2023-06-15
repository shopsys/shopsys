import { FormLineError } from '../Lib/FormLineError';
import { Textarea, TextareaProps } from './Textarea';
import { ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type TextareaControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    textareaProps: Pick<TextareaProps, 'disabled' | 'label' | 'required' | 'rows'>;
    control: Control<any>;
    formName: string;
};

export const TextareaControlled: FC<TextareaControlledProps> = ({ name, render, control, formName, textareaProps }) => {
    const {
        fieldState: { invalid, error },
        field,
    } = useController({ name, control });
    const textareaId = formName + '-' + name;

    return render(
        <>
            <Textarea {...textareaProps} {...field} id={textareaId} hasError={invalid} />
            <FormLineError error={error} inputType="textarea" dataTestId={`${textareaId}-error`} />
        </>,
    );
};
