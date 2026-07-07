import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { AnimatePresence, m } from 'framer-motion';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';

type DrawerProps = {
    title: string;
    isActive: boolean;
    setIsActive: (value: boolean) => void;
};

export const Drawer: FC<DrawerProps> = ({ title, isActive, setIsActive, children, className }) => {
    const isDesktop = useMediaMin('vl');
    const { t } = useTranslation();
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
                        'pointer-events-auto fixed top-0 right-0 z-aboveOverlay h-dvh min-w-79 max-w-md overflow-y-auto rounded-none bg-background-default p-5',
                        className,
                    )}
                    onClick={(event) => event.stopPropagation()}
                    onTouchEnd={(event) => event.stopPropagation()}
                >
                    <div className="mb-5 flex flex-row justify-between">
                        <span className="w-full text-center text-md">{title}</span>

                        <button
                            className="flex cursor-pointer items-center justify-center p-1 text-icon-less"
                            tabIndex={0}
                            title={t('Close')}
                            onClick={() => setIsActive(false)}
                        >
                            <RemoveIcon className="size-4" />
                        </button>
                    </div>

                    {children}
                </m.div>
            )}
        </AnimatePresence>
    );
};
