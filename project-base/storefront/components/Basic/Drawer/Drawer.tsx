import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { AnimatePresence, m } from 'framer-motion';
import { twMergeCustom } from 'utils/twMerge';

type DrawerProps = {
    title: string;
    isClicked: boolean;
    setIsClicked: (value: boolean) => void;
};

export const Drawer: FC<DrawerProps> = ({ title, isClicked, setIsClicked, children, className }) => {
    return (
        <AnimatePresence initial={false}>
            {isClicked && (
                <m.div
                    animate={{ translateX: '0%' }}
                    exit={{ translateX: '100%' }}
                    initial={{ translateX: '100%' }}
                    transition={{ duration: 0.2 }}
                    className={twMergeCustom(
                        'pointer-events-auto  fixed right-0 top-0 z-aboveOverlay h-dvh min-w-[315px] rounded-none bg-background p-5',
                        className,
                    )}
                >
                    <div className="mb-10 flex flex-row justify-between pr-1">
                        <span className="w-full text-center text-base">{title}</span>
                        <RemoveIcon
                            className="w-4 cursor-pointer text-borderAccent"
                            onClick={() => setIsClicked(false)}
                        />
                    </div>
                    {children}
                </m.div>
            )}
        </AnimatePresence>
    );
};
