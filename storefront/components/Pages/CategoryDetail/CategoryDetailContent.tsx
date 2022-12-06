import { AdvancedSeoCategories } from './AdvancedSeoCategories/AdvancedSeoCategories';
import {
    CategoryDetailAdvertsStyled,
    CategoryDetailContentStyled,
    CategoryDetailDescriptionStyled,
    CategoryDetailPanelIconStyled,
    CategoryDetailPanelOpenerStyled,
    CategoryDetailPanelStyled,
    CategoryDetailStyled,
    SubcategoriesSimpleNavigationStyled,
} from './CategoryDetailContent.style';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { Heading } from 'components/Basic/Heading/Heading';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { Filter } from 'components/Blocks/Product/Filter/Filter';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useRef, useState } from 'react';
import { CategoryDetailType } from 'types/category';

type CategoryDetailContentProps = {
    category: CategoryDetailType;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category }) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const panelWrapRef = useRef<null | HTMLDivElement>(null);
    const buttonRef = useRef<null | HTMLDivElement>(null);
    const { query } = useRouter();
    const isFiltered = 'filter' in query;
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);

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

    if (category.productConnection.productFilterOptions === null) {
        return null;
    }

    return (
        <PaginationProvider key={category.uuid} {...getNewPagination(currentPage)}>
            <FilterProvider
                key={category.slug}
                originalSlug={category.originalCategorySlug}
                productFilterOptions={category.productConnection.productFilterOptions}
            >
                <Webline>
                    {isFiltered && <MetaRobots content="noindex, follow" />}
                    <CategoryDetailStyled ref={containerWrapRef}>
                        <CategoryDetailPanelStyled isOpen={isPanelOpen} ref={panelWrapRef}>
                            <Filter
                                key={category.slug}
                                slug={category.slug}
                                originalSlug={category.originalCategorySlug}
                                orderingMode={category.productConnection.orderingMode}
                                defaultOrderingMode={category.productConnection.defaultOrderingMode}
                            />
                            <Overlay isHiddenOnDesktop onClick={handlePanelOpenerClick} />
                        </CategoryDetailPanelStyled>
                        <CategoryDetailContentStyled>
                            <CategoryDetailAdvertsStyled positionName="productList" />
                            <Heading type={'h1'}>{category.seoH1 !== null ? category.seoH1 : category.name}</Heading>
                            {category.description !== null &&
                                category.description !== '' &&
                                (query[PAGE_QUERY_PARAMETER_NAME] ?? 1) === 1 && (
                                    <CategoryDetailDescriptionStyled
                                        dangerouslySetInnerHTML={{ __html: category.description }}
                                    ></CategoryDetailDescriptionStyled>
                                )}
                            <CategoryDetailAdvertsStyled positionName="productListMiddle" currentCategory={category} />
                            <SubcategoriesSimpleNavigationStyled
                                listedItems={[...category.children, ...category.linkedCategories]}
                            />
                            <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
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
                            <CategoryDetailProductsWrapper category={category} containerWrapRef={containerWrapRef} />
                        </CategoryDetailContentStyled>
                    </CategoryDetailStyled>
                </Webline>
            </FilterProvider>
        </PaginationProvider>
    );
};
