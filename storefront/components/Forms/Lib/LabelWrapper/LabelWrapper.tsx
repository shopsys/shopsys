import { LabelWrapperStyled, RequiredSymbolStyled } from './LabelWrapper.style';
import { FC, LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    /**
     * Display Label of the given HTML element
     */
    label: string | ReactNode | ReactNode[];
    /**
     * Display count of items. This is an optional prop primary from the parameters filter.
     */
    count?: number;
    /**
     * A prop based on which the CSS stzling is applied, as there is a slightly different
     * styling for each of the elements below.
     */
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    /**
     * A prop based on which the "required symbol" (star) is displayed next to the label
     */
    required?: boolean;
    /**
     * Type of placeholder for check if the placeholder is static or adaptive.
     */
    placeholderType?: 'static';
    /**
     * check if checkbox is checked
     */
    checked?: boolean;
    /**
     * Set floating label if the selectbox has a value or if the menu is open.
     */
    selectBoxLabelIsFloated?: boolean;
};

const LabelWrapper: FC<LabelWrapperProps> = (props) => {
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

/* @component */
export default LabelWrapper;
