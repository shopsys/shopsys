import { OverlayStyled } from './Overlay.style';
import { FC, MouseEventHandler } from 'react';

type OverlayProps = {
    onClick: MouseEventHandler;
    isHiddenOnDesktop?: boolean;
};

const TEST_IDENTIFIER = 'basic-overlay';

export const Overlay: FC<OverlayProps> = ({ onClick, children, isHiddenOnDesktop }) => (
    <OverlayStyled onClick={onClick} isHiddenOnDesktop={isHiddenOnDesktop} data-testid={TEST_IDENTIFIER}>
        {children}
    </OverlayStyled>
);
