import { OverlayStyled, OverlayWrapperStyled } from './Overlay.style';
import { FC } from 'react';
import { CSSTransition } from 'react-transition-group';

type OverlayProps = {
    isActive: boolean;
    onCloseHandler: () => void;
};

const TEST_IDENTIFIER = 'layout-overlay';

export const Overlay: FC<OverlayProps> = ({ isActive, onCloseHandler }) => {
    return (
        <OverlayWrapperStyled data-testid={TEST_IDENTIFIER}>
            <CSSTransition in={isActive} timeout={500} classNames="overlay" unmountOnExit>
                <OverlayStyled onClick={onCloseHandler} />
            </CSSTransition>
        </OverlayWrapperStyled>
    );
};
