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
    <div className="relative w-full select-none font-secondary">
        {children}
        {!!label && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' &&
                        'pointer-events-none top-2 text-sm peer-placeholder-shown:top-1/2 peer-placeholder-shown:font-semibold peer-placeholder-shown:text-md peer-focus:top-2 peer-focus:font-normal peer-focus:text-sm',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'peer-focus:translate-none absolute left-3 z-2 block transform-none text-input-placeholder-default peer-placeholder-shown:-translate-y-1/2 peer-hover:text-input-placeholder-hovered peer-focus:text-input-placeholder-active peer-disabled:text-input-placeholder-disabled motion-safe:transition-all',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 font-semibold text-sm',
                        checked
                            ? 'text-link-default hover:text-link-hovered'
                            : 'text-input-text-default hover:text-input-text-hovered',
                        disabled && 'cursor-no-drop text-input-text-disabled opacity-50 hover:text-input-text-disabled',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-link-default [&>a]:hover:text-link-hovered [&>a]:focus:text-link-hovered [&>a]:active:text-link-hovered',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        disabled && 'text-input-placeholder-disabled!',
                    ],
                    inputType === 'textarea' &&
                        'top-1 bg-background-default pr-1 text-sm peer-placeholder-shown:top-6 peer-placeholder-shown:font-semibold peer-placeholder-shown:text-md peer-focus:top-1 peer-focus:font-normal peer-focus:text-sm',
                    disabled && 'text-input-text-disabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <span
                        className={twMergeCustom(
                            'flex size-5 min-w-5 border border-input-border-default bg-input-bg-default p-[3px] transition group-hover:bg-fill-accent-less',
                            inputType === 'checkbox' ? 'rounded-checkbox' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'border-input-border-active bg-input-fill group-hover:bg-input-fill'
                                : 'border-2 group-hover:border-input-fill group-active:border-input-fill',
                            disabled &&
                                'border-input-border-disabled group-hover:border-input-border-disabled group-hover:bg-input-bg-disabled group-active:border-input-border-disabled',
                            disabled && checked && 'bg-input-border-disabled group-hover:bg-input-border-disabled',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                aria-hidden="true"
                                className={twMergeCustom(
                                    'h-full text-icon-inverted opacity-0 transition',
                                    checked && 'opacity-100',
                                    disabled && 'text-input-text-disabled',
                                )}
                            />
                        ) : (
                            <span
                                className={twMergeCustom(
                                    'h-full w-full rounded-full bg-icon-inverted opacity-0 transition',
                                    checked && 'opacity-100',
                                )}
                            />
                        )}
                    </span>
                )}

                <span className="flex w-full min-w-0 justify-between">
                    <span
                        className={twMergeCustom(
                            'w-full min-w-0',
                            (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                                'truncate',
                        )}
                    >
                        {label}
                        {required && (
                            <span aria-hidden="true" className="ml-1 text-text-error">
                                *
                            </span>
                        )}
                    </span>

                    {!!count && !checked && <span className="ml-auto font-normal text-text-less">({count})</span>}
                </span>
            </label>
        )}
    </div>
);
