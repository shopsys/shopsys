import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import { MouseEventHandler } from 'react';
import { fadeAnimation } from 'utils/animations/animationVariants';
import { twMergeCustom } from 'utils/twMerge';

type OverlayProps = {
    isActive: boolean;
    isHiddenOnDesktop?: boolean;
    className?: string;
    onClick?: MouseEventHandler;
};

export const Overlay: FC<OverlayProps> = ({ onClick, isActive, isHiddenOnDesktop, className, children }) => {
    return (
        <AnimatePresence>
            {isActive && (
                <m.div
                    animate="visible"
                    data-tid={TIDs.overlay}
                    exit="hidden"
                    initial="hidden"
                    variants={fadeAnimation}
                    className={twMergeCustom(
                        'fixed inset-0 z-overlay flex cursor-pointer items-center justify-center bg-overlay-default',
                        isHiddenOnDesktop && 'vl:hidden',
                        className,
                    )}
                    onClick={(event) => {
                        if (event.target !== event.currentTarget) {
                            return;
                        }

                        onClick?.(event);
                    }}
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
