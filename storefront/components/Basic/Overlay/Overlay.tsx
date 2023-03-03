import { OverlayStyled, OverlayWrapperStyled } from './Overlay.style';
import { OverlayProps } from './propTypes';
import { CSSTransition } from 'react-transition-group';

export const Overlay: FC<OverlayProps> = (props) => {
    const dataTestId = 'basic-overlay';

    return (
        <OverlayWrapperStyled data-testid={dataTestId}>
            <CSSTransition in timeout={500} classNames="overlay" unmountOnExit>
                <OverlayStyled {...props} onClick={props.onClick}>
                    {props.children}
                </OverlayStyled>
            </CSSTransition>
        </OverlayWrapperStyled>
    );
};
