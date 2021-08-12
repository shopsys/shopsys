import { InputHTMLAttributes, ReactElement, useEffect, useState } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import {
    StyledShopsysInputFormLine,
    StyledShopsysPasswordVisibilityToggle,
    StyledShopsysTextInput,
} from './ShopsysTextInput.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { getStateAfterValidation } from '../helpers/getStateAfterValidation';
import ShopsysFormLineError from '../lib/ShopsysFormLineError';
import ShopsysLabelWrapper from '../lib/ShopsysLabelWrapper';
import { useFormContext } from 'react-hook-form';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'id',
    'disabled' | 'required'
>;

/**
 * An HTML Input element used for text inputs of types: text, password, email, tel,
 */
function ShopsysTextInput(props: InferProps<typeof ShopsysTextInput.propTypes> & NativeProps): ReactElement {
    const { register, formState } = useFormContext();
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);
    const [inputType, setInputType] = useState(props.type);

    const togglePasswordVisibilityHandler = () => {
        setInputType(inputType === 'text' ? 'text' : 'password');
    };

    useEffect(() => {
        setInputState(getStateAfterValidation(formState, props.name, props.markSuccessfulWhenValid));
    }, [formState.touchedFields[props.name], formState.errors[props.name], props.markSuccessfulWhenValid]);

    return (
        <StyledShopsysInputFormLine className="text-input">
            <ShopsysLabelWrapper
                htmlFor={props.id}
                required={props.required}
                label={props.label}
                inputType="text-input"
            >
                <StyledShopsysTextInput
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    {...props}
                    inputState={inputState}
                    inputSize={props.inputSize}
                    type={inputType}
                    placeholder={props.label}
                />
                {props.type === 'password' && (
                    <StyledShopsysPasswordVisibilityToggle
                        src="/svg/eye.svg"
                        className={inputType === 'password' ? 'not-visible' : undefined}
                        onClick={togglePasswordVisibilityHandler}
                    />
                )}
            </ShopsysLabelWrapper>
            <ShopsysFormLineError
                inputType="text-input"
                textInputSize={props.inputSize}
                errors={formState.errors}
                for={props.name}
            />
        </StyledShopsysInputFormLine>
    );
}

ShopsysTextInput.defaultProps = {
    type: 'text',
    inputSize: 'default',
    markSuccessfulWhenValid: false,
};

ShopsysTextInput.propTypes = {
    /**
     * Display Label of the HTML input element
     */
    label: PropTypes.string.isRequired,
    /**
     * A enumerator-like list of all available types of the custom TextInput element
     * @see https://www.w3schools.com/html/html_form_input_types.asp
     */
    type: PropTypes.oneOf<'text' | 'password' | 'email' | 'tel'>(['text', 'password', 'email', 'tel']).isRequired,
    /**
     * A enumerator-like list of all available sizes of the custom TextInput element
     */
    inputSize: PropTypes.oneOf<'default' | 'small'>(['default', 'small']).isRequired,
    /**
     * A prop to define if the HTML textarea element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid: PropTypes.bool.isRequired,
};

/* @component */
export default ShopsysTextInput;
