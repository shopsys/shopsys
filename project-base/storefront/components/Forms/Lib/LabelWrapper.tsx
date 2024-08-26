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
                        'top-2 text-sm peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-sm',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'absolute left-3 z-[2] block transition-all font-bold text-inputPlaceholder peer-disabled:text-inputPlaceholderDisabled peer-focus:text-inputPlaceholderActive peer-hover:text-inputPlaceholderHovered',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        (selectBoxLabelIsFloated === undefined || selectBoxLabelIsFloated === true) &&
                        'transform-none peer-placeholder-shown:-translate-y-1/2 peer-focus:transform-none',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold hover:text-inputTextActive',
                        checked ? 'text-inputTextActive' : 'text-inputText',
                        disabled && 'cursor-no-drop text-inputTextDisabled opacity-60',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-link [&>a]:hover:text-linkHovered [&>a]:focus:text-linkHovered [&>a]:active:text-linkHovered',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        selectBoxLabelIsFloated && 'top-[9px] text-sm',
                    ],
                    inputType === 'textarea' &&
                        'top-1 text-sm peer-placeholder-shown:top-6 peer-placeholder-shown:text-md font-semibold peer-focus:top-1 peer-focus:text-sm',
                    disabled && 'text-inputTextDisabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <div
                        className={twMergeCustom(
                            'flex size-5 min-w-5 border border-inputBorder bg-inputBackground p-[3px] transition',
                            inputType === 'checkbox' ? 'rounded' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'border-inputBorderActive bg-inputBorderActive'
                                : 'group-hover:border-inputBorderHovered border-2 group-active:border-inputBorderHovered',
                            disabled &&
                                'border-inputBorderDisabled outline-0 group-hover:border-inputBorderDisabled group-hover:bg-inputBorderDisabled group-active:border-inputBorderDisabled group-active:outline-0 active:scale-100',
                            disabled && checked && 'bg-inputBorderDisabled group-hover:bg-inputBorderDisabled',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                className={twMergeCustom(
                                    'h-full opacity-0 transition text-inputTextInverted',
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

                <div className="w-full flex justify-between">
                    <div className="w-full">{label}</div>
                    {required && <div className="ml-1 text-textError">*</div>}
                    {!!count && !checked && <div className="text-inputPlaceholder font-normal ml-auto">({count})</div>}
                </div>
            </label>
        )}
    </div>
);
