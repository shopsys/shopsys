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
    const itemsLabel = t('products count', { count: showMoreCount });

    return (
        <div className="bg-background-more relative rounded-xl p-5">
            <div className="font-secondary mb-3 text-center text-lg font-semibold break-words">
                {t('Do not want to choose? Choose certainty')}
            </div>

            <div className="divide-border-less mb-3 flex flex-col divide-y">
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
                        className="font-secondary text-link-default hover:text-link-hovered cursor-pointer text-sm font-semibold underline"
                        onClick={() => setIsCollapsed((prev) => !prev)}
                    >
                        {isCollapsed
                            ? t('Show {{ count }} more {{ items }}', { count: showMoreCount, items: itemsLabel })
                            : t('Show less')}
                    </button>
                </div>
            )}
        </div>
    );
};
