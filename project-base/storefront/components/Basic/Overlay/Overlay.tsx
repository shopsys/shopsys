import { MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type OverlayProps = {
    isActive: boolean;
    isHiddenOnDesktop?: boolean;
    onClick?: MouseEventHandler;
};

export const Overlay: FC<OverlayProps> = ({ onClick, isActive, isHiddenOnDesktop, children }) => {
    return (
        <div
            className={twMergeCustom(
                'fixed inset-0 z-overlay flex cursor-pointer items-center justify-center bg-black bg-opacity-60 opacity-0 transition-opacity',
                isActive ? 'opacity-100' : 'pointer-events-none opacity-0',
                isHiddenOnDesktop && 'vl:hidden',
            )}
            onClick={onClick}
        >
            {children}
        </div>
    );
};
