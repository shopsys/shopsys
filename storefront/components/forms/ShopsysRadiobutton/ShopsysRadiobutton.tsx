import { InputHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysChoiceFormLine, StyledShopsysRadiobutton } from './ShopsysRadiobutton.style';
import { OptionalExceptFor } from 'typeHelpers/OptionalExceptFor';
import ShopsysLabelWrapper from '../lib/ShopsysLabelWrapper';
import { useFormContext } from 'react-hook-form';

type NativeProps = OptionalExceptFor<
    Pick<InputHTMLAttributes<HTMLInputElement>, 'id' | 'name' | 'value' | 'disabled'>,
    'id' | 'name' | 'value'
>;

/**
 * An HTML Radiobutton element of type radiobutton
 */
function ShopsysRadiobutton(props: InferProps<typeof ShopsysRadiobutton.propTypes> & NativeProps): ReactElement {
    const { register } = useFormContext();

    return (
        <StyledShopsysChoiceFormLine>
            <ShopsysLabelWrapper
                htmlFor={props.id}
                label={
                    <div>
                        {props.image && <img alt="" src={props.image} />}
                        <span>{props.label}</span>
                    </div>
                }
            >
                <StyledShopsysRadiobutton {...register(props.name)} {...props} type="radio" />
            </ShopsysLabelWrapper>
        </StyledShopsysChoiceFormLine>
    );
}

ShopsysRadiobutton.propTypes = {
    /**
     * Display Label of the HTML radiobutton element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
    /**
     * A prop which, if present, provides a URL for an image
     * which then gets rendered next to the label
     */
    image: PropTypes.string,
};

/* @component */
export default ShopsysRadiobutton;
