import { twMergeCustom } from 'helpers/twMerge';
import { MouseEventHandler, useEffect } from 'react';

type OverlayProps = {
    isActive: boolean;
    isHiddenOnDesktop?: boolean;
    onClick?: MouseEventHandler;
};

export const Overlay: FC<OverlayProps> = ({ onClick, isActive, isHiddenOnDesktop, children }) => {
    useEffect(() => {
        if (isActive) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }

        return () => {
            document.body.style.overflow = 'auto';
        };
    }, [isActive]);

    return (
        <div
            className={twMergeCustom(
                'fixed inset-0 z-overlay flex cursor-pointer items-center justify-center bg-black bg-opacity-60 opacity-0 transition-opacity',
                isActive ? 'opacity-100' : 'pointer-events-none opacity-0',
                isHiddenOnDesktop && 'vl:hidden',
            )}
            onClick={onClick}
            onMouseDown={(event) => {
                event.stopPropagation();
            }}
            onTouchMove={(event) => {
                event.stopPropagation();
            }}
        >
            {children}
        </div>
    );
};
