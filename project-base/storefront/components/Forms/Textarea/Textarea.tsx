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
                        'peer w-full resize-y appearance-none rounded-md border-2 border-inputBorder bg-inputBackground px-[10px] py-5 font-bold text-inputText placeholder:opacity-0 hover:border-inputBorderHovered focus:border-inputTextActive focus:outline-none disabled:pointer-events-none disabled:cursor-no-drop disabled:opacity-50',
                        '[&:-internal-autofill-selected]:!bg-inputBackground [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-inputBackground [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-inputBackgroundHovered [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-inputBackgroundActive [&:-webkit-autofill]:focus:!shadow-inner',
                        hasError && 'border-inputError shadow-none',
                    )}
                    onBlur={onBlur}
                    onChange={onChange}
                />
            </LabelWrapper>
        );
    },
);

Textarea.displayName = 'Textarea';
