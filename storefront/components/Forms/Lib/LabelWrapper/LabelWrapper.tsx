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

export const LabelWrapper: FC<LabelWrapperProps> = (props) => {
    return (
        <LabelWrapperStyled inputType={props.inputType} selectBoxLabelIsFloated={props.selectBoxLabelIsFloated}>
            {props.children}
            {props.placeholderType !== 'static' && (
                <label htmlFor={props.htmlFor}>
                    {props.label}
                    {props.count !== undefined &&
                        props.checked === false &&
                        props.count > 0 &&
                        `\u00A0(${props.count})`}
                    {props.required && <RequiredSymbolStyled>*</RequiredSymbolStyled>}
                </label>
            )}
        </LabelWrapperStyled>
    );
};
