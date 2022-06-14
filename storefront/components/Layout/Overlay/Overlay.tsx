import { OverlayStyled, OverlayWrapperStyled } from './Overlay.style';
import { FC } from 'react';
import { CSSTransition } from 'react-transition-group';

type OverlayProps = {
    isActive: boolean;
};

const TEST_IDENTIFIER = 'layout-overlay';

const Overlay: FC<OverlayProps> = ({ isActive }) => {
    return (
        <OverlayWrapperStyled data-testid={TEST_IDENTIFIER}>
            <CSSTransition in={isActive} timeout={500} classNames="overlay" unmountOnExit>
                <OverlayStyled />
            </CSSTransition>
        </OverlayWrapperStyled>
    );
};

export default Overlay;
