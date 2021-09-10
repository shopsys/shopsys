import { FC, TextareaHTMLAttributes, useEffect, useState } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { getStateAfterValidation } from '../Helpers/getStateAfterValidation';
import LabelWrapper from '../Lib/LabelWrapper';
import { TextareaStyled } from './Textarea.style';

type NativeProps = ExtractNativePropsFromDefault<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'rows',
    'disabled' | 'required' | 'name' | 'id' | 'style'
>;

type TextareaProps = NativeProps & {
    /**
     * Display Label of the HTML textarea element
     */
    label: string;
    /**
     * A prop to decide if the input has errors
     */
    hasError: boolean;
    /**
     * A prop to decide if the input is touched
     */
    isTouched: boolean;
    /**
     * A prop to define if the HTML textarea element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid?: boolean;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
};

/**
 * An HTML Textarea element
 */
const Textarea: FC<TextareaProps> = (props) => {
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);

    useEffect(() => {
        setInputState(getStateAfterValidation(props.hasError, props.isTouched, props.markSuccessfulWhenValid));
    }, [props.hasError, props.isTouched, props.markSuccessfulWhenValid]);

    return (
        <LabelWrapper {...props} htmlFor={props.id} inputType="textarea">
            <TextareaStyled {...props.fieldRef} {...props} inputState={inputState} placeholder={props.label} />
        </LabelWrapper>
    );
};

/* @component */
export default Textarea;
