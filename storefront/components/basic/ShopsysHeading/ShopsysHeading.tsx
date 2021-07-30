import { CSSProperties, ReactElement, ReactNode } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import {
    StyledShopsysHeading1,
    StyledShopsysHeading2,
    StyledShopsysHeading3,
    StyledShopsysHeading4,
} from './ShopsysHeading.style';

const renderHeading = (type: 'h1' | 'h2' | 'h3' | 'h4') => {
    switch (type) {
        case 'h1':
            return StyledShopsysHeading1;
        case 'h2':
            return StyledShopsysHeading2;
        case 'h3':
            return StyledShopsysHeading3;
        case 'h4':
            return StyledShopsysHeading4;
    }

    throw new Error('Wrong type provided for ShopsysHeading.');
};

/**
 * A global heading element, which takes a "type" prop, and based on that displays a heading of type h1 - h4
 */
function ShopsysHeading(
    props: InferProps<typeof ShopsysHeading.propTypes> & { style?: CSSProperties; children: ReactNode },
): ReactElement {
    const Component = renderHeading(props.type);
    return <Component {...props}>{props.children}</Component>;
}

ShopsysHeading.propTypes = {
    /**
     * A enumerator-like list of all available types of the custom Heading element
     */
    type: PropTypes.oneOf<'h1' | 'h2' | 'h3' | 'h4'>(['h1', 'h2', 'h3', 'h4']).isRequired,
};

/* @component */
export default ShopsysHeading;
