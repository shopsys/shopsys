import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    label: string | ReactNode | ReactNode[] | undefined;
    count?: number;
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    required?: boolean;
    checked?: boolean;
    disabled?: boolean;
    selectBoxLabelIsFloated?: boolean;
};

export const LabelWrapper: FC<LabelWrapperProps> = ({
    label,
    count,
    inputType,
    required,
    checked,
    disabled,
    selectBoxLabelIsFloated,
    htmlFor,
    children,
    className,
}) => (
    <div className="relative w-full select-none">
        {children}
        {!!label && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' &&
                        'top-2 text-xs peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'absolute left-3 z-[2] block text-sm transition-all text-inputPlaceholder peer-disabled:text-inputPlaceholderDisabled peer-focus:text-inputPlaceholderActive peer-hover:text-inputPlaceholderHovered',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        (selectBoxLabelIsFloated === undefined || selectBoxLabelIsFloated === true) &&
                        'transform-none peer-placeholder-shown:-translate-y-1/2 peer-focus:transform-none',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-base text-inputText',
                        disabled && 'cursor-no-drop text-inputTextDisabled opacity-60',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-link [&>a]:hover:text-linkHovered [&>a]:focus:text-linkHovered [&>a]:active:text-linkHovered',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        selectBoxLabelIsFloated && 'top-[9px] text-xs',
                    ],
                    inputType === 'textarea' &&
                        'top-2 text-xs peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-xs',
                    disabled && 'text-inputTextDisabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <div
                        className={twMergeCustom(
                            'flex w-5 h-5 min-w-5 border border-inputBorder bg-inputBackground p-[3px] transition',
                            inputType === 'checkbox' ? 'rounded' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'border-inputBorderActive bg-inputBorderActive'
                                : 'group-hover:border-inputBorderHovered group-active:border-inputBorderHovered group-hover:bg-inputBorderHovered',
                            disabled &&
                                'border-inputBorderDisabled outline-0 group-hover:border-inputBorderDisabled group-hover:bg-inputBorderDisabled group-active:border-inputBorderDisabled group-active:outline-0 active:scale-100',
                            disabled && checked && 'bg-inputBorderDisabled group-hover:bg-inputBorderDisabled',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                className={twMergeCustom(
                                    'h-full opacity-0 transition group-hover:opacity-100 text-inputTextInverted',
                                    checked && 'opacity-100',
                                    disabled && 'text-inputTextDisabled',
                                )}
                            />
                        ) : (
                            <span
                                className={twMergeCustom(
                                    'h-full w-full rounded-full bg-inputTextInverted opacity-0 transition',
                                    checked && 'opacity-100',
                                )}
                            />
                        )}
                    </div>
                )}

                <div className="w-full">
                    {label}
                    {!!count && !checked && ` (${count})`}
                    {required && <span className="ml-1 text-textError">*</span>}
                </div>
            </label>
        )}
    </div>
);
