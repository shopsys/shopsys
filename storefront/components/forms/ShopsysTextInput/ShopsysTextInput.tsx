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
        inputType === 'text' ? setInputType('password') : setInputType('text');
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
            <ShopsysFormLineError inputType="text-input" errors={formState.errors} for={props.name} />
        </StyledShopsysInputFormLine>
    );
}

ShopsysTextInput.defaultProps = {
    type: 'text',
    markSuccessfulWhenValid: false,
};

ShopsysTextInput.propTypes = {
    /**
     * Display Label of the HTML input element
     */
    label: PropTypes.string.isRequired,
    /**
     * A enumerator-like list of all available types of the custom TextInput element
     */
    type: PropTypes.oneOf(['text', 'password', 'email', 'tel']).isRequired,
    /**
     * A prop to define if the HTML textarea element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid: PropTypes.bool.isRequired,
};

/* @component */
export default ShopsysTextInput;
