'use client';

import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { useTranslation } from 'components/providers/TranslationProvider';
import { AnimatePresence } from 'framer-motion';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

type CategoryBestselllersRemainingProductsCollapsibleProps = {
    productsLength: number;
};

export const CategoryBestselllersRemainingProductsCollapsible: FC<
    CategoryBestselllersRemainingProductsCollapsibleProps
> = ({ children, productsLength }) => {
    const { t } = useTranslation();
    const [isCollapsed, setIsCollapsed] = useState(true);

    const showMoreCount = productsLength;
    const itemsLabel = t('products count', { count: showMoreCount });

    return (
        <>
            <AnimatePresence initial={false}>
                {!isCollapsed && (
                    <div className="divide-borderAccentLess border-borderAccentLess flex flex-col divide-y border-t">
                        <AnimateCollapseDiv className={twJoin('!block')}>{children}</AnimateCollapseDiv>
                    </div>
                )}
            </AnimatePresence>

            <div className="mt-3 text-center">
                <button
                    className="font-secondary text-link hover:text-linkHovered cursor-pointer text-sm font-semibold underline"
                    onClick={() => setIsCollapsed((prev) => !prev)}
                >
                    {isCollapsed
                        ? t('Show {{ count }} more {{ items }}', { count: showMoreCount, items: itemsLabel })
                        : t('Show less')}
                </button>
            </div>
        </>
    );
};
