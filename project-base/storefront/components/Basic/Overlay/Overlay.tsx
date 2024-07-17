import { AnimatePresence, m } from 'framer-motion';
import { MouseEventHandler } from 'react';
import { fadeAnimation } from 'utils/animations/animationVariants';
import { twMergeCustom } from 'utils/twMerge';

type OverlayProps = {
    isActive: boolean;
    isHiddenOnDesktop?: boolean;
    onClick?: MouseEventHandler;
};

export const Overlay: FC<OverlayProps> = ({ onClick, isActive, isHiddenOnDesktop, children }) => {
    return (
        <AnimatePresence>
            {isActive && (
                <m.div
                    animate="visible"
                    exit="hidden"
                    initial="hidden"
                    variants={fadeAnimation}
                    className={twMergeCustom(
                        'fixed inset-0 z-overlay flex cursor-pointer items-center justify-center bg-black bg-opacity-60',
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
                </m.div>
            )}
        </AnimatePresence>
    );
};
