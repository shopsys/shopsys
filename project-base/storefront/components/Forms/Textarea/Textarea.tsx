import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, ReactNode, TextareaHTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'rows' | 'id',
    'disabled' | 'required' | 'name' | 'onBlur' | 'onChange'
>;

export type TextareaProps = NativeProps & {
    value: any;
    label: ReactNode;
    hasError: boolean;
};

export const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
    ({ label, hasError, rows, disabled, required, name, id, onChange, value, onBlur }, textareaForwardedProps) => {
        return (
            <LabelWrapper htmlFor={id} inputType="textarea" label={label} required={required}>
                <textarea
                    disabled={disabled}
                    id={id}
                    name={name}
                    placeholder={typeof label === 'string' ? label : ' '}
                    ref={textareaForwardedProps}
                    rows={rows}
                    value={value}
                    // class "peer" is used for styling in LabelWrapper
                    className={twJoin(
                        'peer w-full resize-y appearance-none rounded-md border-2 border-skyBlue bg-white py-5 px-[10px] text-dark placeholder:opacity-0 focus:outline-none disabled:pointer-events-none disabled:cursor-no-drop disabled:opacity-50 [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner',
                        hasError && 'border-red bg-white shadow-none',
                    )}
                    onBlur={onBlur}
                    onChange={onChange}
                />
            </LabelWrapper>
        );
    },
);

Textarea.displayName = 'Textarea';
