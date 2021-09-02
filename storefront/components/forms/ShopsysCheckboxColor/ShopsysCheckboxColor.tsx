import { InputHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysCheckboxColor, StyledShopsysChoiceFormLine } from './ShopsysCheckboxColor.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import ShopsysColorLabelWrapper from '../lib/ShopsysColorLabelWrapper';
import ShopsysFormLineError from '../lib/ShopsysFormLineError';
import { useFormContext } from 'react-hook-form';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'name',
    'disabled' | 'required',
>;

/**
 * An HTML Checkbox element of type checkbox
 */
function ShopsysCheckboxColor(props: InferProps<typeof ShopsysCheckboxColor.propTypes> & NativeProps): ReactElement {
    const { register, formState } = useFormContext();

    return (
        <StyledShopsysChoiceFormLine className="checkbox">
            <ShopsysColorLabelWrapper htmlFor={props.id} label={props.label} color={props.color}>
                <StyledShopsysCheckboxColor
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    {...props}
                    type="checkbox"
                />
            </ShopsysColorLabelWrapper>
            <ShopsysFormLineError inputType="checkbox" errors={formState.errors} for={props.name} />
        </StyledShopsysChoiceFormLine>
    );
}

ShopsysCheckboxColor.propTypes = {
    /**
     * Display Label of the HTML checkbox element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),

    /**
     * Define checkbox color background
     */ 
    color: PropTypes.string.isRequired,
};

/* @component */
export default ShopsysCheckboxColor;
