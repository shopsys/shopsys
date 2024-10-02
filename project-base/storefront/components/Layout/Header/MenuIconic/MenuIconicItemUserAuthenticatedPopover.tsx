import { AnimateAppearDiv } from 'components/Basic/Animations/AnimateAppearDiv';
import { AnimatePresence } from 'framer-motion';
import { twMergeCustom } from 'utils/twMerge';

type MenuIconicItemUserAuthenticatedPopoverProps = {
    isHovered: boolean;
};

export const MenuIconicItemUserAuthenticatedPopover: FC<MenuIconicItemUserAuthenticatedPopoverProps> = ({
    isHovered,
    children,
}) => {
    return (
        <AnimatePresence initial={false}>
            {isHovered && (
                <AnimateAppearDiv
                    className={twMergeCustom(
                        'pointer-events-auto absolute -right-[100%] top-[54px] z-cart hidden min-w-[355px] origin-top',
                        'rounded-xl bg-background p-5 lg:block',
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
