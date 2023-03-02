import { LabelWrapperStyled } from './LabelWrapper.style';
import { LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    label: string | ReactNode | ReactNode[];
    count?: number;
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    required?: boolean;
    placeholderType?: 'static';
    checked?: boolean;
    selectBoxLabelIsFloated?: boolean;
};

export const LabelWrapper: FC<LabelWrapperProps> = ({
    label,
    count,
    inputType,
    required,
    placeholderType,
    checked,
    selectBoxLabelIsFloated,
    htmlFor,
    children,
    className,
}) => {
    return (
        <LabelWrapperStyled
            className={className}
            inputType={inputType}
            selectBoxLabelIsFloated={selectBoxLabelIsFloated}
        >
            {children}
            {placeholderType !== 'static' && (
                <label htmlFor={htmlFor}>
                    {label}
                    {count !== undefined && checked === false && count > 0 && `\u00A0(${count})`}
                    {required && <span className="ml-1 text-red">*</span>}
                </label>
            )}
        </LabelWrapperStyled>
    );
};
