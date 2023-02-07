import { MouseEventHandler } from 'react';

export type OverlayProps = {
    $isActive?: boolean;
    $isHeaderVisible?: boolean;
    $isHiddenOnDesktop?: boolean;
    onClick?: MouseEventHandler;
};
