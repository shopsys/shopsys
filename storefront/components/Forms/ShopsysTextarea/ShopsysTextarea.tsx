import PropTypes, { InferProps } from 'prop-types';
import { ReactElement, TextareaHTMLAttributes, useEffect, useState } from 'react';
import { StyledShopsysTextarea, StyledShopsysTextareaFormLine } from './ShopsysTextarea.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { getStateAfterValidation } from '../Helpers/getStateAfterValidation';
import ShopsysFormLineError from '../Lib/ShopsysFormLineError';
import ShopsysLabelWrapper from '../Lib/ShopsysLabelWrapper';
import { useFormContext } from 'react-hook-form';

type NativeProps = ExtractNativePropsFromDefault<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'name' | 'id',
    'disabled' | 'required' | 'rows'
>;

/**
 * An HTML Textarea element
 */
function ShopsysTextarea(props: InferProps<typeof ShopsysTextarea.propTypes> & NativeProps): ReactElement {
    const { register, formState } = useFormContext();
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);

    useEffect(() => {
        setInputState(getStateAfterValidation(formState, props.name, props.markSuccessfulWhenValid));
    }, [formState.touchedFields[props.name], formState.errors[props.name], props.markSuccessfulWhenValid]);

    return (
        <StyledShopsysTextareaFormLine>
            <ShopsysLabelWrapper htmlFor={props.id} required={props.required} label={props.label} inputType="textarea">
                <StyledShopsysTextarea
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    {...props}
                    inputState={inputState}
                    placeholder={props.label}
                />
            </ShopsysLabelWrapper>
            <ShopsysFormLineError inputType="textarea" errors={formState.errors} for={props.name} />
        </StyledShopsysTextareaFormLine>
    );
}

ShopsysTextarea.defaultProps = {
    rows: 4,
    markSuccessfulWhenValid: false,
};

ShopsysTextarea.propTypes = {
    /**
     * Display Label of the HTML textarea element
     */
    label: PropTypes.string.isRequired,
    /**
     * A prop to define if the HTML textarea element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid: PropTypes.bool.isRequired,
};

/* @component */
export default ShopsysTextarea;
