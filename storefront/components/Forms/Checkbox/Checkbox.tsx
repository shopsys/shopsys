import { FC, InputHTMLAttributes, ReactNode } from 'react';
import { CheckboxStyled } from './Checkbox.style';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import LabelWrapper from '../Lib/LabelWrapper';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    never,
    'id' | 'name' | 'disabled' | 'required'
>;

type CheckboxProps = NativeProps & {
    /**
     * A prop to decide if the input has errors
     */
    hasError: boolean;
    /**
     * A prop to decide if the input is touched
     */
    isTouched: boolean;
    /**
     * Display Label of the HTML checkbox element
     */
    label: string | ReactNode | ReactNode[];
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
};

/**
 * An HTML Checkbox element of type checkbox
 */
const Checkbox: FC<CheckboxProps> = (props) => {
    return (
        <LabelWrapper {...props} htmlFor={props.id} inputType="checkbox">
            <CheckboxStyled {...props} {...props.fieldRef} checked={props.fieldRef?.value} type="checkbox" />
        </LabelWrapper>
    );
};

/* @component */
export default Checkbox;
