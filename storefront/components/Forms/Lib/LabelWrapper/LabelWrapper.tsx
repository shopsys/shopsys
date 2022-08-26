import { LabelWrapperStyled, RequiredSymbolStyled } from './LabelWrapper.style';
import { FC, LabelHTMLAttributes, ReactNode } from 'react';
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
}) => {
    return (
        <LabelWrapperStyled inputType={inputType} selectBoxLabelIsFloated={selectBoxLabelIsFloated}>
            {children}
            {placeholderType !== 'static' && (
                <label htmlFor={htmlFor}>
                    {label}
                    {count !== undefined && checked === false && count > 0 && `\u00A0(${count})`}
                    {required && <RequiredSymbolStyled>*</RequiredSymbolStyled>}
                </label>
            )}
        </LabelWrapperStyled>
    );
};
