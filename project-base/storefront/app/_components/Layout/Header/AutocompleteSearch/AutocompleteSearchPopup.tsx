'use client';

import { motion } from 'framer-motion';
import { RemoveScroll } from 'react-remove-scroll';
import { twJoin } from 'tailwind-merge';
import { fadeAnimation } from 'utils/animations/animationVariants';

type AutocompleteSearchPopupProps = {
    handleClosePopup: () => void;
};

export const AutocompleteSearchPopup: FC<AutocompleteSearchPopupProps> = ({ children, handleClosePopup }) => {
    return (
        <RemoveScroll>
            <motion.div
                animate="visible"
                exit="hidden"
                initial="hidden"
                variants={fadeAnimation}
                className={twJoin(
                    'z-aboveOverlay bg-background-default origin-top translate-y-full overflow-auto rounded-xl p-8',
                    'absolute -bottom-3 left-0',
                    'vl:gap-7 flex flex-col gap-5',
                    'vl:w-[700px] w-full',
                    'vl:max-h-[calc(98vh-120px)] max-h-[calc(85vh-169px)] md:max-h-[calc(98vh-169px)] lg:max-h-[calc(98vh-180px)]',
                )}
                onClick={handleClosePopup}
            >
                {children}
            </motion.div>
        </RemoveScroll>
    );
};
