import { TextareaStyled } from './Textarea.style';
import { getStateAfterValidation } from 'components/Forms/Helpers/getStateAfterValidation';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { FC, TextareaHTMLAttributes, useEffect, useState } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'rows',
    'disabled' | 'required' | 'name' | 'id' | 'style'
>;

type TextareaProps = NativeProps & {
    label: string;
    hasError: boolean;
    isTouched: boolean;
    markSuccessfulWhenValid?: boolean;
    fieldRef?: ControllerRenderProps<any, any>;
};

export const Textarea: FC<TextareaProps> = ({
    label,
    hasError,
    isTouched,
    markSuccessfulWhenValid,
    fieldRef,
    rows,
    disabled,
    required,
    name,
    id,
    style,
}) => {
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);

    useEffect(() => {
        setInputState(getStateAfterValidation(hasError, isTouched, markSuccessfulWhenValid));
    }, [hasError, isTouched, markSuccessfulWhenValid]);

    return (
        <LabelWrapper label={label} htmlFor={id} required={required} inputType="textarea">
            <TextareaStyled
                id={id}
                rows={rows}
                disabled={disabled}
                name={name}
                style={style}
                inputState={inputState}
                placeholder={label}
                {...fieldRef}
            />
        </LabelWrapper>
    );
};
