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
                        'z-aboveOverlay bg-background-default pointer-events-auto fixed top-0 right-0 h-dvh min-w-[315px] overflow-y-auto rounded-none p-5',
                        className,
                    )}
                >
                    <div className="flex flex-row justify-between">
                        <span className="w-full text-center text-base">{title}</span>
                        <RemoveIcon
                            className="text-border-default w-4 cursor-pointer"
                            onClick={() => setIsActive(false)}
                        />
                    </div>
                    {children}
                </m.div>
            )}
        </AnimatePresence>
    );
};
