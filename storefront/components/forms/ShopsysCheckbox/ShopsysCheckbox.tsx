import { InputHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysCheckbox, StyledShopsysChoiceFormLine } from './ShopsysCheckbox.style';
import { OptionalExceptFor } from 'typeHelpers/OptionalExceptFor';
import ShopsysFormLineError from '../lib/ShopsysFormLineError';
import ShopsysLabelWrapper from '../lib/ShopsysLabelWrapper';
import { useFormContext } from 'react-hook-form';

type NativeProps = OptionalExceptFor<
    Pick<InputHTMLAttributes<HTMLInputElement>, 'id' | 'name' | 'disabled' | 'required'>,
    'name' | 'id'
>;

/**
 * An HTML Checkbox element of type checkbox
 */
function ShopsysCheckbox(props: InferProps<typeof ShopsysCheckbox.propTypes> & NativeProps): ReactElement {
    const { register, formState } = useFormContext();

    return (
        <StyledShopsysChoiceFormLine className="checkbox">
            <ShopsysLabelWrapper htmlFor={props.id} required={props.required} label={props.label}>
                <StyledShopsysCheckbox {...register(props.name)} {...props} type="checkbox" />
            </ShopsysLabelWrapper>
            <ShopsysFormLineError htmlElement="checkbox" errors={formState.errors} for={props.name} />
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
