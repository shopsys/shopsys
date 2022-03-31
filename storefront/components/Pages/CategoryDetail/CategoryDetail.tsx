import {
    CategoryDetailAdvertsStyled,
    CategoryDetailContentMessageStyled,
    CategoryDetailContentStyled,
    CategoryDetailPanelIconStyled,
    CategoryDetailPanelOpenerStyled,
    CategoryDetailPanelStyled,
    CategoryDetailStyled,
    SubcategoriesSimpleNavigationStyled,
} from './CategoryDetail.style';
import { FC, useEffect, useRef, useState } from 'react';
import CategoryDetailAdvancedSeoCategories from './CategoryDetailAdvancedSeoCategories';
import { CategoryDetailType } from 'types/category';
import { FilterOptionsType } from 'types/productFilter';
import Heading from 'components/Basic/Heading';
import Overlay from 'components/Basic/Overlay';
import Pagination from 'components/Blocks/Pagination/Pagination';
import ProductFilter from 'components/Blocks/Product/Filter';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import Trans from 'next-translate/Trans';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type CategoryDetailProps = {
    category: CategoryDetailType;
};

const CategoryDetail: FC<CategoryDetailProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const panelWrapRef = useRef<null | HTMLDivElement>(null);
    const buttonRef = useRef<null | HTMLDivElement>(null);
    const [productFilterOptionsData, setProductFilterOptionsData] = useState<FilterOptionsType>(
        props.category.productConnection.productFilterOptions as FilterOptionsType,
    );
    const [categorySlug, setCategorySlug] = useState(props.category.slug);

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

    useEffect(() => {
        if (props.category.productConnection.productFilterOptions !== null) {
            setProductFilterOptionsData(props.category.productConnection.productFilterOptions);
            setCategorySlug(props.category.slug);
        }
    }, [props.category.productConnection.productFilterOptions]);

    return (
        <Webline>
            <CategoryDetailStyled ref={containerWrapRef}>
                <CategoryDetailPanelStyled isOpen={isPanelOpen} ref={panelWrapRef}>
                    <ProductFilter productFilterOptions={productFilterOptionsData} slug={categorySlug} />
                    <Overlay isHiddenOnDesktop={true} onClick={handlePanelOpenerClick} />
                </CategoryDetailPanelStyled>
                <CategoryDetailContentStyled>
                    <CategoryDetailAdvertsStyled positionName="productList" />
                    <Heading type={'h1'}>
                        {props.category.seoH1 !== null ? props.category.seoH1 : props.category.name}
                    </Heading>
                    <CategoryDetailAdvertsStyled positionName="productListMiddle" currentCategory={props.category} />
                    <SubcategoriesSimpleNavigationStyled
                        listedItems={[...props.category.children, ...props.category.linkedCategories]}
                    />
                    <CategoryDetailAdvancedSeoCategories
                        readyCategorySeoMixLinks={props.category.readyCategorySeoMixLinks}
                    />
                    <CategoryDetailPanelOpenerStyled
                        id="js-category-detail-panel"
                        ref={buttonRef}
                        onClick={handlePanelOpenerClick}
                        isOpen={isPanelOpen}
                    >
                        <CategoryDetailPanelIconStyled iconType="icon" icon="Filter" />
                        {t('Filter')}
                    </CategoryDetailPanelOpenerStyled>
                    <SortingBar totalCount={props.category.productConnection.totalCount} />
                    {props.category.productConnection.products.length !== 0 ? (
                        <ProductsList products={props.category.productConnection.products} />
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
                        totalCount={props.category.productConnection.totalCount}
                        containerWrapRef={containerWrapRef}
                    />
                </CategoryDetailContentStyled>
            </CategoryDetailStyled>
        </Webline>
    );
};

export default CategoryDetail;
