import { FC, InputHTMLAttributes, ReactNode } from 'react';
import { CheckboxStyled } from './Checkbox.style';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name',
    'id' | 'disabled' | 'required'
>;

type CheckboxProps = NativeProps & {
    /**
     * A prop to decide if the input has errors
     */
    hasError?: boolean;
    /**
     * A prop to decide if the input is touched
     */
    isTouched?: boolean;
    /**
     * Display Label of the HTML checkbox element
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
};

/**
 * An HTML Checkbox element of type checkbox
 */
const Checkbox: FC<CheckboxProps> = (props) => {
    return (
        <LabelWrapper
            {...props}
            htmlFor={props.id === undefined ? props.name + 'checkbox-id' : props.id}
            inputType="checkbox"
            checked={props.fieldRef?.value}
        >
            <CheckboxStyled
                {...props}
                {...props.fieldRef}
                id={props.id === undefined ? props.name + 'checkbox-id' : props.id}
                checked={props.fieldRef?.value}
                type="checkbox"
            />
        </LabelWrapper>
    );
};

/* @component */
export default Checkbox;
