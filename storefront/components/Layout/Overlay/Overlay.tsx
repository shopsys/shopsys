import { OverlayStyled, OverlayWrapperStyled } from './Overlay.style';
import { CSSTransition } from 'react-transition-group';
import { FC } from 'react';

type OverlayProps = {
    isActive: boolean;
};

const Overlay: FC<OverlayProps> = (props) => {
    const testIdentifier = 'layout-overlay';

    return (
        <OverlayWrapperStyled data-testid={testIdentifier}>
            <CSSTransition in={props.isActive} timeout={500} classNames="overlay" unmountOnExit>
                <OverlayStyled />
            </CSSTransition>
        </OverlayWrapperStyled>
    );
};

export default Overlay;
