import { LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'helpers/visual/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    label: string | ReactNode | ReactNode[];
    count?: number;
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    required?: boolean;
    isWithoutLabel?: boolean;
    checked?: boolean;
    selectBoxLabelIsFloated?: boolean;
};

export const LabelWrapper: FC<LabelWrapperProps> = ({
    label,
    count,
    inputType,
    required,
    isWithoutLabel,
    checked,
    selectBoxLabelIsFloated,
    htmlFor,
    children,
    className,
}) => (
    <div className="relative w-full">
        {children}
        {!isWithoutLabel && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' &&
                        ' top-2  text-xs peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'absolute left-3 z-[2] block text-sm text-grey transition-all',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        (selectBoxLabelIsFloated === undefined || selectBoxLabelIsFloated === true) &&
                        'transform-none peer-placeholder-shown:-translate-y-1/2 peer-focus:transform-none',
                    inputType === 'checkbox' && [
                        'relative inline-block min-h-[18px] cursor-pointer pl-8 text-sm text-dark before:absolute before:top-0 before:left-0 before:inline-block before:h-[18px] before:w-[18px] before:bg-[url("/images/custom_checkbox.png")] before:bg-[length:54px_36px] before:bg-left-top before:bg-no-repeat before:content-[""]',
                        'peer-checked:before:!bg-bottom peer-hover:before:bg-top peer-active:before:bg-top peer-disabled:pointer-events-none peer-disabled:cursor-no-drop peer-disabled:text-greyLight peer-disabled:opacity-50 peer-disabled:before:cursor-no-drop peer-disabled:before:bg-right',
                        '[&>a]:text-dark [&>a]:hover:text-orange [&>a]:focus:text-orange [&>a]:active:text-orange',
                    ],
                    inputType === 'radio' && [
                        'cursor-pointer before:absolute before:top-1/2 before:left-0 before:inline-block before:h-[18px] before:w-[18px] before:-translate-y-1/2 before:bg-[url("/images/custom_radio.png")] before:bg-[length:54px_36px] before:bg-left-top before:bg-no-repeat before:content-[""] hover:before:bg-top [&>div]:relative [&>div]:flex [&>div]:min-h-[18px] [&>div]:items-center [&>div]:pl-[30px]',
                        'peer-checked:before:bg-bottom peer-focus:before:bg-top peer-checked:peer-focus:before:bg-bottom peer-disabled:cursor-no-drop peer-disabled:before:bg-right-top  peer-checked:peer-disabled:before:bg-right-bottom  peer-disabled:[&>div>span]:cursor-no-drop peer-disabled:[&>div>span]:text-greyLight peer-disabled:[&>div>img]:cursor-no-drop peer-disabled:[&>div>img]:grayscale peer-disabled:[&>div>img]:filter',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        selectBoxLabelIsFloated && 'top-[9px] text-xs',
                    ],
                    inputType === 'textarea' && 'top-5 translate-y-0 peer-focus:top-2 peer-focus:text-xs',
                    className,
                )}
            >
                {label}
                {count !== undefined && checked === false && count > 0 && `\u00A0(${count})`}
                {required && <span className="ml-1 text-red">*</span>}
            </label>
        )}
    </div>
);
