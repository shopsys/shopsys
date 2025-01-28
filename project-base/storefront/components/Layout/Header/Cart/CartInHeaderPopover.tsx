'use client';

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
                        'pointer-events-auto absolute right-[-15px] top-[54px] z-cart hidden p-5 vl:block',
                        'right-0 h-auto min-w-[315px] origin-top-right rounded-lg bg-background',
                        isCartEmpty ? 'hidden w-96 flex-nowrap items-center justify-center vl:flex' : 'w-[548px]',
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
