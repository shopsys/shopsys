import {
    CategoryDetailAdvertsStyled,
    CategoryDetailContentMessageStyled,
    CategoryDetailContentStyled,
    CategoryDetailDescriptionStyled,
    CategoryDetailPanelIconStyled,
    CategoryDetailPanelOpenerStyled,
    CategoryDetailPanelStyled,
    CategoryDetailStyled,
    SubcategoriesSimpleNavigationStyled,
} from './CategoryDetail.style';
import CategoryDetailAdvancedSeoCategories from './CategoryDetailAdvancedSeoCategories';
import Heading from 'components/Basic/Heading';
import Overlay from 'components/Basic/Overlay';
import Pagination from 'components/Blocks/Pagination/Pagination';
import ProductFilter from 'components/Blocks/Product/Filter';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import Webline from 'components/Layout/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { FC, useMemo, useRef, useState } from 'react';
import { CategoryDetailType } from 'types/category';
import { FilterOptionsType } from 'types/productFilter';
import { getCategoryOrSeoCategoryGtmListName } from 'utils/Gtm/Gtm';

type CategoryDetailProps = {
    category: CategoryDetailType;
};

const CategoryDetail: FC<CategoryDetailProps> = ({ category }) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const panelWrapRef = useRef<null | HTMLDivElement>(null);
    const buttonRef = useRef<null | HTMLDivElement>(null);

    const handlePanelOpenerClick = () => {
        setIsPanelOpen(!isPanelOpen);

        let newPosition = 0;
        const newPositionOffset = 20;

        if (buttonRef.current !== null) {
            newPosition = buttonRef.current.offsetTop + buttonRef.current.clientHeight + newPositionOffset;
        }

        if (panelWrapRef.current !== null) {
            panelWrapRef.current.style.cssText = 'top: ' + newPosition + 'px';
        }
    };

    const gtmListName = useMemo(() => getCategoryOrSeoCategoryGtmListName(category, category.slug), [category]);

    return (
        <Webline>
            <CategoryDetailStyled ref={containerWrapRef}>
                <CategoryDetailPanelStyled isOpen={isPanelOpen} ref={panelWrapRef}>
                    <ProductFilter
                        productFilterOptions={category.productConnection.productFilterOptions as FilterOptionsType}
                        slug={category.slug}
                    />
                    <Overlay isHiddenOnDesktop={true} onClick={handlePanelOpenerClick} />
                </CategoryDetailPanelStyled>
                <CategoryDetailContentStyled>
                    <CategoryDetailAdvertsStyled positionName="productList" />
                    <Heading type={'h1'}>{category.seoH1 !== null ? category.seoH1 : category.name}</Heading>
                    {category.description !== null && category.description !== '' && (
                        <CategoryDetailDescriptionStyled
                            dangerouslySetInnerHTML={{ __html: category.description }}
                        ></CategoryDetailDescriptionStyled>
                    )}
                    <CategoryDetailAdvertsStyled positionName="productListMiddle" currentCategory={category} />
                    <SubcategoriesSimpleNavigationStyled
                        listedItems={[...category.children, ...category.linkedCategories]}
                    />
                    <CategoryDetailAdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                    <CategoryDetailPanelOpenerStyled
                        id="js-category-detail-panel"
                        ref={buttonRef}
                        onClick={handlePanelOpenerClick}
                        isOpen={isPanelOpen}
                    >
                        <CategoryDetailPanelIconStyled iconType="icon" icon="Filter" />
                        {t('Filter')}
                    </CategoryDetailPanelOpenerStyled>
                    <SortingBar
                        sorting={category.productConnection.orderingMode}
                        totalCount={category.productConnection.totalCount}
                    />
                    {category.productConnection.products.length !== 0 ? (
                        <ProductsList products={category.productConnection.products} gtmListName={gtmListName} />
                    ) : (
                        <CategoryDetailContentMessageStyled>
                            <div>
                                <strong>{t('No results match the filter')}</strong>
                            </div>
                            <div>
                                <Trans i18nKey="ProductsNoResults" components={{ 0: <br /> }} />
                            </div>
                        </CategoryDetailContentMessageStyled>
                    )}
                    <Pagination
                        totalCount={category.productConnection.totalCount}
                        containerWrapRef={containerWrapRef}
                    />
                </CategoryDetailContentStyled>
            </CategoryDetailStyled>
        </Webline>
    );
};

export default CategoryDetail;
