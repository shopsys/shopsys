import { FC, MouseEventHandler } from 'react';
import { OverlayStyled } from './Overlay.style';

type OverlayProps = {
    onClick: MouseEventHandler;
    isHiddenOnDesktop?: boolean;
};
/**
 * A global overlay element
 */
const Overlay: FC<OverlayProps> = (props) => {
    const testIdentifier = 'basic-overlay';

    return (
        <OverlayStyled {...props} data-testid={testIdentifier}>
            {props.children}
        </OverlayStyled>
    );
};

export default Overlay;
