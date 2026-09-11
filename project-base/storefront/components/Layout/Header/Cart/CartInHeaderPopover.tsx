import { AnimateAppearDiv } from 'components/Basic/Animations/AnimateAppearDiv';
import { AnimatePresence } from 'framer-motion';
import { twMergeCustom } from 'utils/twMerge';

type CartInHeaderPopoverProps = {
    isActive: boolean;
    isCartEmpty: boolean;
};

export const CartInHeaderPopover: FC<CartInHeaderPopoverProps> = ({ children, isActive, isCartEmpty }) => {
    return (
        <AnimatePresence initial={false}>
            {isActive && (
                <AnimateAppearDiv
                    className={twMergeCustom(
                        'pointer-events-auto absolute top-14 -right-3.75 z-cart vl:block hidden p-5',
                        'right-0 h-auto min-w-78 origin-top-right rounded-lg bg-background-default',
                        'before:absolute before:-top-5 before:left-0 before:h-5 before:w-full',
                        isCartEmpty ? 'vl:flex hidden w-96 flex-nowrap items-center justify-center' : 'w-137',
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
