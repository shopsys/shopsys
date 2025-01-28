'use client';

import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { AnimatePresence, m } from 'framer-motion';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';

type DrawerProps = {
    title: string;
    isActive: boolean;
    setIsActive: (value: boolean) => void;
};

export const Drawer: FC<DrawerProps> = ({ title, isActive, setIsActive, children, className }) => {
    const isDesktop = useMediaMin('vl');

    if (isDesktop) {
        return null;
    }

    return (
        <AnimatePresence initial={false}>
            {isActive && (
                <m.div
                    animate={{ translateX: '0%' }}
                    exit={{ translateX: '100%' }}
                    initial={{ translateX: '100%' }}
                    transition={{ duration: 0.2 }}
                    className={twMergeCustom(
                        'pointer-events-auto fixed right-0 top-0 z-aboveOverlay h-dvh min-w-[315px] overflow-y-auto rounded-none bg-background p-5',
                        className,
                    )}
                >
                    <div className="mb-10 flex flex-row justify-between pr-1">
                        <span className="w-full text-center text-base">{title}</span>
                        <RemoveIcon
                            className="w-4 cursor-pointer text-borderAccent"
                            onClick={() => setIsActive(false)}
                        />
                    </div>
                    {children}
                </m.div>
            )}
        </AnimatePresence>
    );
};
