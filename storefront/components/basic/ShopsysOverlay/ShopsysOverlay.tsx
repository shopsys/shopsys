import { ReactElement } from 'react';
import { ShopsysOverlayStyled } from './ShopsysOverlay.style';

/**
 * A global heading element, which takes a "type" prop, and based on that displays a heading of type h1 - h4
 */
function ShopsysOverlay(props): ReactElement {
    return <ShopsysOverlayStyled {...props}>{props.children}</ShopsysOverlayStyled>;
}

export default ShopsysOverlay;
