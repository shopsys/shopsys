import { OverlayStyled } from './Overlay.style';
import { FC, MouseEventHandler } from 'react';

type OverlayProps = {
    onClick: MouseEventHandler;
    isHiddenOnDesktop?: boolean;
};

export const Overlay: FC<OverlayProps> = (props) => {
    const testIdentifier = 'basic-overlay';

    return (
        <OverlayStyled {...props} data-testid={testIdentifier}>
            {props.children}
        </OverlayStyled>
    );
};
