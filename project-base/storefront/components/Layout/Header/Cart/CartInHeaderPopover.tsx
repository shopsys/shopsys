import { AnimateAppearDiv } from 'components/Basic/Animations/AnimateAppearDiv';
import { AnimatePresence } from 'framer-motion';
import { twMergeCustom } from 'utils/twMerge';

type CartInHeaderPopoverProps = {
    isHovered: boolean;
    isCartEmpty: boolean;
};

export const CartInHeaderPopover: FC<CartInHeaderPopoverProps> = ({ children, isHovered, isCartEmpty }) => {
    return (
        <AnimatePresence initial={false}>
            {isHovered && (
                <AnimateAppearDiv
                    className={twMergeCustom(
                        'pointer-events-auto absolute right-[-15px] top-[54px] z-cart hidden p-5 lg:block',
                        'right-0 h-auto min-w-[315px] origin-top-right rounded-lg bg-background',
                        isCartEmpty ? 'flex w-96 flex-nowrap items-center justify-between' : 'w-[548px]',
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
