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
    <div className="relative w-full select-none font-secondary">
        {children}
        {!!label && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' &&
                        'top-2 text-sm peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:font-semibold peer-focus:top-2 peer-focus:text-sm peer-focus:font-normal',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'absolute left-3 z-[2] block text-inputPlaceholder transition-all peer-hover:text-inputPlaceholderHovered peer-focus:text-inputPlaceholderActive peer-disabled:text-inputPlaceholderDisabled',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        (selectBoxLabelIsFloated === undefined || selectBoxLabelIsFloated === true) &&
                        'transform-none peer-placeholder-shown:-translate-y-1/2 peer-focus:transform-none',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-sm font-semibold',
                        checked
                            ? 'text-inputTextActive hover:text-inputTextActive'
                            : 'text-inputText hover:text-inputTextHovered',
                        disabled && 'cursor-no-drop text-inputTextDisabled opacity-60 hover:text-inputTextDisabled',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-link [&>a]:hover:text-linkHovered [&>a]:focus:text-linkHovered [&>a]:active:text-linkHovered',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        selectBoxLabelIsFloated && 'top-[9px] text-sm',
                    ],
                    inputType === 'textarea' &&
                        'top-1 pr-1 text-sm peer-placeholder-shown:top-6 peer-placeholder-shown:text-base peer-placeholder-shown:font-semibold peer-focus:top-1 peer-focus:text-sm peer-focus:font-normal',
                    disabled && 'text-inputTextDisabled',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <div
                        className={twMergeCustom(
                            'flex size-5 min-w-5 border border-inputBorder bg-inputBackground p-[3px] transition group-hover:bg-inputBackgroundHovered ',
                            inputType === 'checkbox' ? 'rounded' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'border-inputBorderActive bg-inputBackgroundActive group-hover:bg-inputBackgroundActive '
                                : 'border-2 group-hover:border-inputBorderHovered group-active:border-inputBorderHovered',
                            disabled &&
                                'border-inputBorderDisabled group-hover:border-inputBorderDisabled group-hover:bg-inputBackgroundDisabled group-active:border-inputBorderDisabled',
                            disabled && checked && 'bg-inputBorderDisabled group-hover:bg-inputBorderDisabled',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                className={twMergeCustom(
                                    'h-full text-inputTextInverted opacity-0 transition',
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

                <div className="flex w-full justify-between">
                    <div className="w-full">{label}</div>
                    {required && <div className="ml-1 text-textError">*</div>}
                    {!!count && !checked && <div className="ml-auto font-normal text-inputPlaceholder">({count})</div>}
                </div>
            </label>
        )}
    </div>
);
