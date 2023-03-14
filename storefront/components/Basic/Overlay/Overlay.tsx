import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';

type OverlayProps = {
    isActive?: boolean;
    isHiddenOnDesktop?: boolean;
    onClick?: MouseEventHandler;
};

const TEST_IDENTIFIER = 'basic-overlay';

export const Overlay: FC<OverlayProps> = ({ onClick, isActive, isHiddenOnDesktop, children }) => {
    return (
        <div
            data-testid={TEST_IDENTIFIER}
            className={twJoin(
                'pointer-events-none fixed inset-0 bottom-0 z-overlay flex cursor-pointer items-center justify-center bg-black bg-opacity-60 opacity-0 transition-opacity',
                isActive && 'opacity-100',
                isHiddenOnDesktop && 'vl:hidden',
            )}
            onClick={onClick}
        >
            {children}
        </div>
    );
};
