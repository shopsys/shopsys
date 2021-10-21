import { FC, LabelHTMLAttributes, ReactNode } from 'react';
import { LabelWrapperStyled, RequiredSymbolStyled } from './LabelWrapper.style';
import { ControllerRenderProps } from 'react-hook-form';
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
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
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
                    {props.count !== undefined && props.fieldRef?.value === false && `\u00A0(${props.count})`}
                    {props.required && <RequiredSymbolStyled>*</RequiredSymbolStyled>}
                </label>
            )}
        </LabelWrapperStyled>
    );
};

/* @component */
export default LabelWrapper;
