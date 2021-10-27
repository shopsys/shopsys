import {
    CategoryDetailContentMessageStyled,
    CategoryDetailContentStyled,
    CategoryDetailPanelIconStyled,
    CategoryDetailPanelOpenerStyled,
    CategoryDetailPanelStyled,
    CategoryDetailStyled,
    SubcategoriesSimpleNavigationStyled,
} from './CategoryDetail.style';
import { FC, useRef, useState } from 'react';
import CategoryDetailAdvancedSeoCategories from './CategoryDetailAdvancedSeoCategories';
import { CategoryDetailType } from './types';
import Heading from 'components/Basic/Heading';
import Overlay from 'components/Basic/Overlay';
import Pagination from 'components/Blocks/Pagination/Pagination';
import ProductFilter from 'components/Blocks/Product/Filter';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import { Trans } from 'react-i18next';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type CategoryDetailProps = {
    category: CategoryDetailType;
};

const CategoryDetail: FC<CategoryDetailProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
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

    return (
        <Webline>
            <CategoryDetailStyled>
                <CategoryDetailPanelStyled isOpen={isPanelOpen} ref={panelWrapRef}>
                    <ProductFilter productFilterOptions={props.category.products.productFilterOptions} />
                    <Overlay isHiddenOnDesktop={true} onClick={handlePanelOpenerClick} />
                </CategoryDetailPanelStyled>
                <CategoryDetailContentStyled>
                    <Heading type={'h1'}>
                        {props.category.seoH1 !== null ? props.category.seoH1 : props.category.name}
                    </Heading>
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
                    >
                        <CategoryDetailPanelIconStyled iconType="icon" icon="Filter" />
                        {t('Filtrovat')}
                    </CategoryDetailPanelOpenerStyled>
                    <SortingBar totalCount={props.category.products.totalCount} />
                    {props.category.products.edges.length !== 0 ? (
                        <ProductsList products={props.category.products.edges.map((edge) => edge.node)} />
                    ) : (
                        <CategoryDetailContentMessageStyled>
                            <div>
                                <strong>{t('No results match the filter')}</strong>
                            </div>
                            <div>
                                <Trans i18nKey="ProductsNoResults">
                                    We currently have no results for your exact search.
                                    <br />
                                    Try to be more specific, or see if you have filtered out non-existent data.
                                </Trans>
                            </div>
                        </CategoryDetailContentMessageStyled>
                    )}
                    <Pagination totalCount={props.category.products.totalCount} />
                </CategoryDetailContentStyled>
            </CategoryDetailStyled>
        </Webline>
    );
};

export default CategoryDetail;
