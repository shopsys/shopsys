import { CategoryBestsellersListItem } from './CategoryBestsellersListItem';
import { Button } from 'components/Forms/Button/Button';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useGtmSliderProductListViewEvent } from 'gtm/hooks/productList/useGtmSliderProductListViewEvent';
import { GtmProductListNameType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

const NUMBER_OF_VISIBLE_ITEMS = 3;

type CategoryBestsellersProps = {
    products: ListedProductFragmentApi[];
};

export const CategoryBestsellers: FC<CategoryBestsellersProps> = ({ products }) => {
    const { t } = useTranslation();
    const [isCollapsed, setIsCollapsed] = useState(true);
    const shownProducts = products.filter((_, index) => index + 1 <= NUMBER_OF_VISIBLE_ITEMS || !isCollapsed);

    useGtmSliderProductListViewEvent(shownProducts, GtmProductListNameType.bestsellers);

    return (
        <div className="mt-6">
            <div className="mb-3 break-words font-bold text-dark lg:text-lg">
                {t('Do not want to choose? Choose certainty')}
            </div>

            <div className="mb-5">
                {shownProducts.map((product, index) => (
                    <CategoryBestsellersListItem
                        key={product.uuid}
                        gtmProductListName={GtmProductListNameType.bestsellers}
                        listIndex={index}
                        product={product}
                    />
                ))}
            </div>

            {products.length > NUMBER_OF_VISIBLE_ITEMS && (
                <div className="text-center">
                    <Button size="small" onClick={() => setIsCollapsed((prev) => !prev)}>
                        <span>{isCollapsed ? t('show more') : t('show less')}</span>
                    </Button>
                </div>
            )}
        </div>
    );
};
