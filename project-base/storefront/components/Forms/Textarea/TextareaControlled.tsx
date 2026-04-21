import { FormLine, FormLineWidth } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { ChangeEventHandler, FocusEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';
import { Textarea, TextareaProps } from './Textarea';

type TextareaControlledProps = {
    name: string;
    render?: (input: ReactElement) => ReactElement<any, any> | null;
    width?: FormLineWidth;
    textareaProps: Pick<TextareaProps, 'disabled' | 'label' | 'required' | 'rows' | 'onBlur' | 'onChange'>;
    control: Control<any>;
    formName: string;
};

export const TextareaControlled: FC<TextareaControlledProps> = ({
    name,
    render,
    width,
    control,
    formName,
    textareaProps,
}) => {
    const {
        fieldState: { error },
        field,
    } = useController({ name, control });
    const textareaId = `${formName}-${name}`;

    const onBlurHandler: FocusEventHandler<HTMLTextAreaElement> = (event) => {
        field.onBlur();

        if (textareaProps.onBlur) {
            textareaProps.onBlur(event);
        }

        window.getSelection()?.removeAllRanges();
    };

    const onChangeHandler: ChangeEventHandler<HTMLTextAreaElement> = (event) => {
        field.onChange(event);

        if (textareaProps.onChange) {
            textareaProps.onChange(event);
        }
    };

    const element = (
        <>
            <Textarea
                {...textareaProps}
                {...field}
                hasError={!!error}
                id={textareaId}
                onBlur={onBlurHandler}
                onChange={onChangeHandler}
            />
            <FormLineError error={error} inputType="textarea" />
        </>
    );

    if (render) {
        return render(element);
    }

    return <FormLine width={width}>{element}</FormLine>;
};
