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
                        'text-input-placeholder-default peer-hover:text-input-placeholder-hovered peer-focus:text-input-placeholder-active peer-disabled:text-input-placeholder-disabled absolute left-3 z-[2] block transform-none peer-placeholder-shown:-translate-y-1/2 peer-focus:translate-none motion-safe:transition-all',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold',
                        checked
                            ? 'text-link-default hover:text-link-hovered'
                            : 'text-input-text-default hover:text-input-text-hovered',
                        disabled && 'text-input-text-disabled hover:text-input-text-disabled cursor-no-drop opacity-60',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-link-default [&>a]:hover:text-link-hovered [&>a]:focus:text-link-hovered [&>a]:active:text-link-hovered',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        disabled && '!text-input-placeholder-disabled',
                    ],
                    inputType === 'textarea' &&
                        'bg-background-default top-1 pr-1 text-sm peer-placeholder-shown:top-6 peer-placeholder-shown:text-base peer-placeholder-shown:font-semibold peer-focus:top-1 peer-focus:text-sm peer-focus:font-normal',
                    disabled && 'text-input-text-disabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <span
                        className={twMergeCustom(
                            'border-input-border-default bg-input-bg-default group-hover:bg-fill-accent-less flex size-5 min-w-5 border p-[3px] transition',
                            inputType === 'checkbox' ? 'rounded-checkbox' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'bg-input-fill group-hover:bg-input-fill border-input-border-active'
                                : 'group-hover:border-input-fill group-active:border-input-fill border-2',
                            disabled &&
                                'border-input-border-disabled group-hover:border-input-border-disabled group-hover:bg-input-bg-disabled group-active:border-input-border-disabled',
                            disabled && checked && 'bg-input-border-disabled group-hover:bg-input-border-disabled',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                aria-hidden="true"
                                className={twMergeCustom(
                                    'text-icon-inverted h-full opacity-0 transition',
                                    checked && 'opacity-100',
                                    disabled && 'text-input-text-disabled',
                                )}
                            />
                        ) : (
                            <span
                                className={twMergeCustom(
                                    'bg-icon-inverted h-full w-full rounded-full opacity-0 transition',
                                    checked && 'opacity-100',
                                )}
                            />
                        )}
                    </span>
                )}

                <span className="flex w-full justify-between">
                    <span className="w-full">
                        {label}
                        {required && (
                            <span aria-hidden="true" className="text-text-error ml-1">
                                *
                            </span>
                        )}
                    </span>

                    {!!count && !checked && (
                        <span className="text-input-placeholder-default ml-auto font-normal">({count})</span>
                    )}
                </span>
            </label>
        )}
    </div>
);
