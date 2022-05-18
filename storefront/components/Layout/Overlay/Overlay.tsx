import { OverlayStyled, OverlayWrapperStyled } from './Overlay.style';
import { FC } from 'react';
import { CSSTransition } from 'react-transition-group';

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
