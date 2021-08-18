import PropTypes, { InferProps } from 'prop-types';
import {
    ShopsysSpinboxButtonStyled,
    ShopsysSpinboxInputStyled,
    ShopsysSpinboxSmallStyled,
    ShopsysSpinboxStyled,
} from './ShopsysSpinbox.style';
import { ReactElement } from 'react';

/**
 * Global component for spinbox input.
 */
function ShopsysSpinbox(props: InferProps<typeof ShopsysSpinbox.propTypes>): ReactElement {
    let Component = ShopsysSpinboxStyled;

    if (props.size === 'small') {
        Component = ShopsysSpinboxSmallStyled;
    }

    return (
        <Component {...props}>
            <ShopsysSpinboxButtonStyled>-</ShopsysSpinboxButtonStyled>
            <ShopsysSpinboxInputStyled defaultValue="1" />
            <ShopsysSpinboxButtonStyled>+</ShopsysSpinboxButtonStyled>
        </Component>
    );
}

ShopsysSpinbox.defaultProps = {
    size: 'default',
};

ShopsysSpinbox.propTypes = {
    /**
     * A enumerator-like list of all available sizes of the custom TextInput element
     */
    size: PropTypes.oneOf<'default' | 'small'>(['default', 'small']).isRequired,
};

/* @component */
export default ShopsysSpinbox;
