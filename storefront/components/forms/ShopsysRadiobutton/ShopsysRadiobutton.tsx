import PropTypes, { InferProps } from 'prop-types';
import {
    StyledShopsysChoiceFormLine,
    StyledShopsysRadiobutton,
    StyledShopsysRadiobuttonImage,
    StyledShopsysRadiobuttonLabel,
} from './ShopsysRadiobutton.style';
import { ReactElement } from 'react';
import { useFormContext } from 'react-hook-form';

/**
 * An HTML Radiobutton element of type radiobutton
 */
function ShopsysRadiobutton(props: InferProps<typeof ShopsysRadiobutton.propTypes>): ReactElement {
    const { register } = useFormContext();

    return (
        <StyledShopsysChoiceFormLine>
            <StyledShopsysRadiobutton>
                <input
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    {...props}
                    type="radio"
                />
                <StyledShopsysRadiobuttonLabel>
                    {props.image && <StyledShopsysRadiobuttonImage alt="" src={props.image} />}
                    <label htmlFor={props.id}>{props.label}</label>
                </StyledShopsysRadiobuttonLabel>
            </StyledShopsysRadiobutton>
        </StyledShopsysChoiceFormLine>
    );
}

ShopsysRadiobutton.defaultProps = {
    disabled: false,
};

ShopsysRadiobutton.propTypes = {
    /**
     * The ID of the HTML radiobutton element which is used for identification
     */
    id: PropTypes.string.isRequired,
    /**
     * The name of the HTML radiobutton element which is used by React Hook Form to manage the field
     * and group it together with other radiobuttons (options) from the same radiobutton group
     */
    name: PropTypes.string.isRequired,
    /**
     * A prop to define value of the HTML radiobutton element
     */
    value: PropTypes.string.isRequired,
    /**
     * Display Label of the HTML radiobutton element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
    /**
     * A prop to define if the HTML radiobutton element is disabled
     */
    disabled: PropTypes.bool.isRequired,
    /**
     * A prop which, if present, provides a URL for an image
     * which then gets rendered next to the label
     */
    image: PropTypes.string,
};

/* @component */
export default ShopsysRadiobutton;
