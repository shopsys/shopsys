import { AdvancedSeoCategories } from './AdvancedSeoCategories/AdvancedSeoCategories';
import {
    CategoryDetailAdvertsStyled,
    CategoryDetailContentStyled,
    CategoryDetailDescriptionStyled,
    CategoryDetailPanelOpenerStyled,
    CategoryDetailPanelStyled,
    CategoryDetailStyled,
    SubcategoriesSimpleNavigationStyled,
} from './CategoryDetailContent.style';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { HeadingPaginated } from 'components/Basic/Heading/HeadingPaginated';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel/FilterPanel';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useCallback, useRef, useState } from 'react';
import { CategoryDetailType } from 'types/category';

type CategoryDetailContentProps = {
    category: CategoryDetailType;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category }) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const { query } = useRouter();
    const isFiltered = 'filter' in query;

    const handlePanelOpenerClick = useCallback(() => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            body.style.overflow = prev ? 'visible' : 'hidden';
            return !prev;
        });
    }, []);

    if (category.productConnection.productFilterOptions === null) {
        return null;
    }

    return (
        <FilterProvider
            key={category.slug}
            originalSlug={category.originalCategorySlug}
            productFilterOptions={category.productConnection.productFilterOptions}
        >
            <Webline>
                {isFiltered && <MetaRobots content="noindex, follow" />}
                <CategoryDetailStyled ref={containerWrapRef}>
                    <CategoryDetailPanelStyled isOpen={isPanelOpen}>
                        <FilterPanel
                            defaultOrderingMode={category.productConnection.defaultOrderingMode}
                            orderingMode={category.productConnection.orderingMode}
                            originalSlug={category.originalCategorySlug}
                            panelCloseHandler={handlePanelOpenerClick}
                            slug={category.slug}
                            totalCount={category.productConnection.totalCount}
                        />
                    </CategoryDetailPanelStyled>
                    {isPanelOpen && <Overlay $isHiddenOnDesktop onClick={handlePanelOpenerClick} />}
                    <CategoryDetailContentStyled>
                        <CategoryDetailAdvertsStyled positionName="productList" />
                        <HeadingPaginated type={'h1'} totalCount={category.productConnection.totalCount}>
                            {category.seoH1 !== null ? category.seoH1 : category.name}
                        </HeadingPaginated>
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
                        <CategoryDetailPanelOpenerStyled onClick={handlePanelOpenerClick}>
                            <Icon
                                iconType="icon"
                                icon="Filter"
                                width={24}
                                height={24}
                                className="mr-3 font-bold text-white"
                            />
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
    );
};
