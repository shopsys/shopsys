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
                        'pointer-events-auto absolute top-[54px] right-[-15px] z-cart vl:block hidden p-5',
                        'right-0 h-auto min-w-[315px] origin-top-right rounded-lg bg-background-default',
                        isCartEmpty ? 'vl:flex hidden w-96 flex-nowrap items-center justify-center' : 'w-[548px]',
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
