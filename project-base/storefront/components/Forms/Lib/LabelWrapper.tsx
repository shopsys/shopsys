import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    label: string | ReactNode | ReactNode[] | undefined;
    count?: number;
    isCountAbsolute?: boolean;
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    inputSize?: 'small' | 'default';
    required?: boolean;
    checked?: boolean;
    disabled?: boolean;
};

export const LabelWrapper: FC<LabelWrapperProps> = ({
    label,
    count,
    isCountAbsolute = true,
    inputType,
    inputSize,
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
                    inputType === 'text-input' && [
                        'pointer-events-none peer-placeholder-shown:top-1/2 peer-placeholder-shown:font-semibold peer-placeholder-shown:text-md peer-focus:font-normal',
                        inputSize === 'small'
                            ? 'top-1.5 text-xs peer-focus:top-1.5 peer-focus:text-xs'
                            : 'top-2 text-sm peer-focus:top-2 peer-focus:text-sm',
                    ],
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
                        '[&>a]:text-link-default [&>a]:hover:text-link-hovered [&>a]:focus:text-link-hovered [&>a]:active:text-link-active',
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
                            'flex size-5 min-w-5 border border-input-border-default bg-input-bg-default p-0.75 transition',
                            inputType === 'checkbox' ? 'rounded-checkbox' : 'rounded-full p-1.25',
                            checked ? 'border-input-border-active bg-input-fill' : 'border-2',
                            !disabled && [
                                // the halo keeps the hover readable on a checked indicator, where the fill only darkens a little
                                'choice-hover:ring-4 choice-hover:ring-input-glow-hovered active:scale-90',
                                checked
                                    ? 'choice-hover:border-input-fill-hovered choice-hover:bg-input-fill-hovered'
                                    : 'choice-hover:border-input-fill choice-hover:bg-fill-accent-less group-active:border-input-fill',
                            ],
                            disabled && 'border-input-border-disabled',
                            disabled && checked && 'bg-input-border-disabled',
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

                <span className="flex w-full min-w-0 justify-between gap-2">
                    <span
                        className={twMergeCustom(
                            'w-full min-w-0',
                            (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                                'truncate',
                        )}
                    >
                        {label}
                        {required && (
                            <span aria-hidden="true" className="ml-1 text-input-required-star">
                                *
                            </span>
                        )}
                    </span>

                    {!!count && !checked && (
                        <span className="ml-auto font-normal text-text-less">
                            ({!isCountAbsolute && '+'}
                            {count})
                        </span>
                    )}
                </span>
            </label>
        )}
    </div>
);
