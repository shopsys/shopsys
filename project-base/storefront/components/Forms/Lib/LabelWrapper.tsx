import { ChoiceIndicator } from 'components/Forms/Lib/ChoiceIndicator';
import { FieldSize } from 'components/Forms/Lib/fieldUtils';
import { LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    label: string | ReactNode | ReactNode[] | undefined;
    count?: number;
    isCountAbsolute?: boolean;
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    inputSize?: FieldSize;
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
    <div className="relative w-full select-none">
        {children}
        {!!label && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' && [
                        'pointer-events-none peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-md',
                        // The label sits above the value inside the field, which is 40px (small) or 56px (default) high.
                        inputSize === 'small'
                            ? 'top-0.5 text-xs peer-focus:top-0.5 peer-focus:text-xs'
                            : 'top-2 text-xs peer-focus:top-2 peer-focus:text-xs',
                    ],
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'peer-focus:translate-none absolute left-3 z-2 block transform-none text-input-placeholder-default peer-placeholder-shown:-translate-y-1/2 peer-hover:text-input-placeholder-hovered peer-focus:text-input-placeholder-active peer-disabled:text-input-placeholder-disabled motion-safe:transition-all',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-sm',
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
                        'top-1 bg-background-default pr-1 text-sm peer-placeholder-shown:top-6 peer-placeholder-shown:text-md peer-focus:top-1 peer-focus-keyboard:bg-focus-background peer-focus:text-sm peer-disabled:bg-input-bg-disabled',
                    disabled && 'text-input-text-disabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <ChoiceIndicator checked={checked} disabled={disabled} inputType={inputType} />
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
