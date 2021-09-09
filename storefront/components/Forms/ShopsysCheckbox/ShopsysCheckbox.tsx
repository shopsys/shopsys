import { InputHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysCheckbox, StyledShopsysChoiceFormLine } from './ShopsysCheckbox.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import ShopsysFormLineError from '../Lib/ShopsysFormLineError';
import ShopsysLabelWrapper from '../Lib/ShopsysLabelWrapper';
import { useFormContext } from 'react-hook-form';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'name',
    'disabled' | 'required'
>;

/**
 * An HTML Checkbox element of type checkbox
 */
function ShopsysCheckbox(props: InferProps<typeof ShopsysCheckbox.propTypes> & NativeProps): ReactElement {
    const { register, formState } = useFormContext();

    return (
        <StyledShopsysChoiceFormLine className="checkbox">
            <ShopsysLabelWrapper htmlFor={props.id} required={props.required} label={props.label} inputType="checkbox">
                <StyledShopsysCheckbox
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    {...props}
                    type="checkbox"
                />
            </ShopsysLabelWrapper>
            <ShopsysFormLineError inputType="checkbox" errors={formState.errors} for={props.name} />
        </StyledShopsysChoiceFormLine>
    );
}

ShopsysCheckbox.propTypes = {
    /**
     * Display Label of the HTML checkbox element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
};

/* @component */
export default ShopsysCheckbox;
