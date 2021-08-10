import { HTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledContainer, StyledWebline } from './Webline.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, 'children', never>;

function Webline(props: InferProps<typeof Webline.propTypes> & NativeProps): ReactElement {
    return (
        <StyledWebline {...props}>
            <StyledContainer>{props.children}</StyledContainer>
        </StyledWebline>
    );
}

Webline.defaultProps = {
    type: 'default',
};

Webline.propTypes = {
    /**
     * Type for change type of webline. If you don't fill this prop then the webline will be without style.
     */
    type: PropTypes.oneOf<'default' | 'colored' | 'dark' | 'light'>(['default', 'colored', 'dark', 'light']).isRequired,
};

/* @component */
export default Webline;
