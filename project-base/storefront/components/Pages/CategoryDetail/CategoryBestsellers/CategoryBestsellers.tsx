import { CategoryBestsellersListItem } from './CategoryBestsellersListItem';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AnimatePresence } from 'framer-motion';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmSliderProductListViewEvent';
import { useState, useRef, useEffect } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const NUMBER_OF_VISIBLE_ITEMS = 3;

type CategoryBestsellersProps = {
    products: TypeListedProductFragment[];
};

export const CategoryBestsellers: FC<CategoryBestsellersProps> = ({ products }) => {
    const { t } = useTranslation();
    const [isCollapsed, setIsCollapsed] = useState(true);
    const [shouldFocusNewItem, setShouldFocusNewItem] = useState(false);
    const listRef = useRef<HTMLDivElement>(null);
    const shownProducts = products.filter((_, index) => index + 1 <= NUMBER_OF_VISIBLE_ITEMS || !isCollapsed);

    const showMoreCount = products.length - NUMBER_OF_VISIBLE_ITEMS;
    const itemsLabel = t('products count', { count: showMoreCount });
    const showMoreLabel = t('Show {{ count }} more {{ items }}', { count: showMoreCount, items: itemsLabel });
    const showLessLabel = t('Show less');
    const ariaLabel = isCollapsed
        ? t('Show {{ count }} more bestseller {{ items }}', {
              ns: 'accessibility',
              count: showMoreCount,
              items: itemsLabel,
          })
        : t('Show less', { ns: 'accessibility' });

    useEffect(() => {
        if (shouldFocusNewItem && !isCollapsed && listRef.current) {
            const focusableLinks = listRef.current.querySelectorAll('a[tabindex="0"]');
            const targetLink = focusableLinks[NUMBER_OF_VISIBLE_ITEMS] as HTMLElement | undefined;

            if (targetLink) {
                setTimeout(() => {
                    targetLink.focus();
                }, 150);
            }

            setShouldFocusNewItem(false);
        }
    }, [isCollapsed, shouldFocusNewItem]);

    const handleShowMoreClick = () => {
        setIsCollapsed((prev) => {
            if (prev) {
                setShouldFocusNewItem(true);
            }
            return !prev;
        });
    };

    useGtmSliderProductListViewEvent(shownProducts, GtmProductListNameType.bestsellers);

    return (
        <div className="bg-background-more relative rounded-xl p-5">
            <h2 className="sr-only">{t('Bestsellers')}</h2>

            <div className="font-secondary mb-3 text-center text-lg font-semibold break-words">
                {t('Do not want to choose? Choose certainty')}
            </div>

            <div className="divide-border-less mb-3 flex flex-col divide-y" id="bestsellers-list" ref={listRef}>
                <AnimatePresence initial={false}>
                    {shownProducts.map((product, index) => (
                        <AnimateCollapseDiv key={product.uuid} className={twJoin('!block')} keyName={product.uuid}>
                            <CategoryBestsellersListItem
                                key={product.uuid}
                                gtmProductListName={GtmProductListNameType.bestsellers}
                                listIndex={index}
                                product={product}
                            />
                        </AnimateCollapseDiv>
                    ))}
                </AnimatePresence>
            </div>

            {products.length > NUMBER_OF_VISIBLE_ITEMS && (
                <div className="text-center">
                    <button
                        aria-controls="bestsellers-list"
                        aria-expanded={!isCollapsed}
                        aria-label={ariaLabel}
                        className="font-secondary text-link-default hover:text-link-hovered cursor-pointer rounded-sm text-sm font-semibold underline"
                        tabIndex={0}
                        title={t('Toggle bestseller list')}
                        onClick={handleShowMoreClick}
                    >
                        {isCollapsed ? showMoreLabel : showLessLabel}
                    </button>
                </div>
            )}
        </div>
    );
};
