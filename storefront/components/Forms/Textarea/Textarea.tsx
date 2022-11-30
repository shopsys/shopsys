import { TextareaStyled } from './Textarea.style';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { forwardRef, TextareaHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'rows' | 'onChange' | 'id',
    'disabled' | 'required' | 'name' | 'onBlur'
>;

export type TextareaProps = NativeProps & {
    value: any;
    label: string;
    hasError: boolean;
};

export const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
    ({ label, hasError, rows, disabled, required, name, id, onChange, value, onBlur }, textareaForwardedProps) => {
        return (
            <LabelWrapper label={label} htmlFor={id} required={required} inputType="textarea">
                <TextareaStyled
                    id={id}
                    rows={rows}
                    disabled={disabled}
                    name={name}
                    hasError={hasError}
                    placeholder={label}
                    onChange={onChange}
                    value={value}
                    onBlur={onBlur}
                    ref={textareaForwardedProps}
                />
            </LabelWrapper>
        );
    },
);

Textarea.displayName = 'Textarea';
