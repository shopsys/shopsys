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
};

export const LabelWrapper: FC<LabelWrapperProps> = ({
    label,
    count,
    inputType,
    required,
    checked,
    disabled,
    htmlFor,
    children,
    className,
}) => (
    <div className="font-secondary relative w-full select-none">
        {children}
        {!!label && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' &&
                        'pointer-events-none top-2 text-sm peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:font-semibold peer-focus:top-2 peer-focus:text-sm peer-focus:font-normal',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'text-inputPlaceholder peer-hover:text-inputPlaceholderHovered peer-focus:text-inputPlaceholderActive peer-disabled:text-inputPlaceholderDisabled absolute left-3 z-[2] block transform-none transition-all peer-placeholder-shown:-translate-y-1/2 peer-focus:translate-none',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold',
                        checked
                            ? 'text-inputTextActive hover:text-inputTextActive'
                            : 'text-inputText hover:text-inputTextHovered',
                        disabled && 'text-inputTextDisabled hover:text-inputTextDisabled cursor-no-drop opacity-60',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-link-default [&>a]:hover:text-link-hovered [&>a]:focus:text-link-hovered [&>a]:active:text-link-hovered',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        disabled && '!text-inputPlaceholderDisabled',
                    ],
                    inputType === 'textarea' &&
                        'bg-background-default top-1 pr-1 text-sm peer-placeholder-shown:top-6 peer-placeholder-shown:text-base peer-placeholder-shown:font-semibold peer-focus:top-1 peer-focus:text-sm peer-focus:font-normal',
                    disabled && 'text-inputTextDisabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <div
                        className={twMergeCustom(
                            'border-inputBorder bg-inputBackground group-hover:bg-inputBackgroundHovered flex size-5 min-w-5 border p-[3px] transition',
                            inputType === 'checkbox' ? 'rounded-sm' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'bg-inputFill group-hover:bg-inputFill border-inputBorderActive'
                                : 'group-hover:border-inputBorderHovered group-active:border-inputBorderHovered border-2',
                            disabled &&
                                'border-inputBorderDisabled group-hover:border-inputBorderDisabled group-hover:bg-inputBackgroundDisabled group-active:border-inputBorderDisabled',
                            disabled && checked && 'bg-inputBorderDisabled group-hover:bg-inputBorderDisabled',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                className={twMergeCustom(
                                    'text-inputTextInverted h-full opacity-0 transition',
                                    checked && 'opacity-100',
                                    disabled && 'text-inputTextDisabled',
                                )}
                            />
                        ) : (
                            <span
                                className={twMergeCustom(
                                    'bg-inputTextInverted h-full w-full rounded-full opacity-0 transition',
                                    checked && 'opacity-100',
                                )}
                            />
                        )}
                    </div>
                )}

                <div className="flex w-full justify-between">
                    <div className="w-full">
                        {label}
                        {required && <span className="text-text-error ml-1">*</span>}
                    </div>

                    {!!count && !checked && <div className="text-inputPlaceholder ml-auto font-normal">({count})</div>}
                </div>
            </label>
        )}
    </div>
);
