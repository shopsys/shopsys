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
                        'peer w-full resize-y appearance-none rounded-input border-2 border-input-border-default bg-input-bg-default px-[10px] py-5 font-bold text-input-text-default placeholder:opacity-0 hover:border-input-border-hovered focus:border-input-fill focus:outline-hidden disabled:pointer-events-none disabled:cursor-no-drop disabled:opacity-50',
                        '[&:-internal-autofill-selected]:bg-input-bg-default! [&:-internal-autofill-selected]:shadow-inner! [&:-webkit-autofill]:bg-input-bg-default! [&:-webkit-autofill]:shadow-inner! [&:-webkit-autofill]:hover:bg-input-bg-hovered! [&:-webkit-autofill]:hover:shadow-inner! [&:-webkit-autofill]:focus:bg-input-fill! [&:-webkit-autofill]:focus:shadow-inner!',
                        hasError && 'border-input-border-error shadow-none',
                    )}
                    onBlur={onBlur}
                    onChange={onChange}
                />
            </LabelWrapper>
        );
    },
);

Textarea.displayName = 'Textarea';
