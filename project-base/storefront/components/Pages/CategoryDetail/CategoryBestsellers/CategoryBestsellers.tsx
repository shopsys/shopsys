import { CategoryBestsellersListItem } from './CategoryBestsellersListItem';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AnimatePresence } from 'framer-motion';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmSliderProductListViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

const NUMBER_OF_VISIBLE_ITEMS = 3;

type CategoryBestsellersProps = {
    products: TypeListedProductFragment[];
};

export const CategoryBestsellers: FC<CategoryBestsellersProps> = ({ products }) => {
    const { t } = useTranslation();
    const [isCollapsed, setIsCollapsed] = useState(true);
    const shownProducts = products.filter((_, index) => index + 1 <= NUMBER_OF_VISIBLE_ITEMS || !isCollapsed);

    useGtmSliderProductListViewEvent(shownProducts, GtmProductListNameType.bestsellers);

    const showMoreCount = products.length - NUMBER_OF_VISIBLE_ITEMS;

    return (
        <div className="relative mb-5 rounded-xl bg-backgroundMore p-5">
            <div className="mb-3 break-words text-center font-secondary text-lg font-semibold">
                {t('Do not want to choose? Choose certainty')}
            </div>

            <div className="mb-3 flex flex-col divide-y divide-borderAccentLess">
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
                        className="font-secondary text-sm font-semibold text-link underline hover:text-linkHovered"
                        onClick={() => setIsCollapsed((prev) => !prev)}
                    >
                        {isCollapsed ? (
                            <>
                                {t('Show more')} {showMoreCount} {t('products count', { count: showMoreCount })}
                            </>
                        ) : (
                            t('Show less')
                        )}
                    </button>
                </div>
            )}
        </div>
    );
};
