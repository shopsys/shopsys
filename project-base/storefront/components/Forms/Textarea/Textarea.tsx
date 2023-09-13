import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, TextareaHTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

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
                <textarea
                    id={id}
                    rows={rows}
                    disabled={disabled}
                    name={name}
                    placeholder={label}
                    onChange={onChange}
                    value={value}
                    onBlur={onBlur}
                    ref={textareaForwardedProps}
                    // class "peer" is used for styling in LabelWrapper
                    className={twJoin(
                        'peer w-full resize-y appearance-none rounded border-2 border-border bg-white py-5 px-[10px] text-dark placeholder:opacity-0 focus:outline-none disabled:pointer-events-none disabled:cursor-no-drop disabled:opacity-50 [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner',
                        hasError && 'border-red bg-white shadow-none',
                    )}
                />
            </LabelWrapper>
        );
    },
);

Textarea.displayName = 'Textarea';
