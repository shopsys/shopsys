import { OverlayStyled, OverlayWrapperStyled } from './Overlay.style';
import { OverlayProps } from './propTypes';
import { FC } from 'react';
import { CSSTransition } from 'react-transition-group';

export const Overlay: FC<OverlayProps> = (props) => {
    const testIdentifier = 'basic-overlay';

    return (
        <OverlayWrapperStyled data-testid={testIdentifier}>
            <CSSTransition in timeout={500} classNames="overlay" unmountOnExit>
                <OverlayStyled {...props} onClick={props.onClick}>
                    {props.children}
                </OverlayStyled>
            </CSSTransition>
        </OverlayWrapperStyled>
    );
};
