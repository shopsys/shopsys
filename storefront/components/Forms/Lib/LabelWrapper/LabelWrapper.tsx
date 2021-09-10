import { FC, LabelHTMLAttributes, ReactNode } from 'react';
import { LabelWrapperStyled, RequiredSymbolStyled } from './LabelWrapper.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    /**
     * Display Label of the given HTML element
     */
    label: string | ReactNode | ReactNode[];
    /**
     * A prop based on which the CSS stzling is applied, as there is a slightly different
     * styling for each of the elements below.
     */
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio';
    /**
     * A prop based on which the "required symbol" (star) is displayed next to the label
     */
    required?: boolean;
    /**
     * Type of placeholder for check if the placeholder is static or adaptive.
     */
    placeholderType?: 'static';
};

const LabelWrapper: FC<LabelWrapperProps> = (props) => {
    return (
        <LabelWrapperStyled inputType={props.inputType}>
            {props.children}
            {props.placeholderType !== 'static' && (
                <label htmlFor={props.htmlFor}>
                    {props.label}
                    {props.required && <RequiredSymbolStyled>*</RequiredSymbolStyled>}
                </label>
            )}
        </LabelWrapperStyled>
    );
};

/* @component */
export default LabelWrapper;
