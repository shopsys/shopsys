import { OverlayStyled, OverlayWrappStyled } from './Overlay.style';
import { CSSTransition } from 'react-transition-group';
import { ReactElement } from 'react';

type OverlayProps = {
    isActive: boolean;
};

const Overlay: FC<OverlayProps> = (props) => {
    return (
        <OverlayWrapperStyled>
            <CSSTransition in={props.isActive} timeout={500} classNames="overlay" unmountOnExit>
                <OverlayStyled />
            </CSSTransition>
        </OverlayWrappStyled>
    );
};

export default Overlay;
