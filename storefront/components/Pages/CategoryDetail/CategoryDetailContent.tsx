import { AdvancedSeoCategories } from './AdvancedSeoCategories/AdvancedSeoCategories';
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
} from './CategoryDetailContent.style';
import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { Heading } from 'components/Basic/Heading/Heading';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { Filter } from 'components/Blocks/Product/Filter/Filter';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { getCategoryOrSeoCategoryGtmListName } from 'helpers/gtm/gtm';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useGtmCategoryProductListView } from 'hooks/gtm/useGtmCategoryProductListView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { useRouter } from 'next/router';
import { FC, useMemo, useRef, useState } from 'react';
import { CategoryDetailType } from 'types/category';

type CategoryDetailContentProps = {
    category: CategoryDetailType;
    fetching: boolean;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category, fetching }) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const panelWrapRef = useRef<null | HTMLDivElement>(null);
    const buttonRef = useRef<null | HTMLDivElement>(null);
    const { query } = useRouter();
    const isFiltered = 'filter' in query;
    const router = useRouter();
    useGtmCategoryProductListView(category, getUrlWithoutGetParameters(router.asPath), fetching);

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
            {isFiltered && <MetaRobots content="noindex, follow" />}
            <CategoryDetailStyled ref={containerWrapRef}>
                <CategoryDetailPanelStyled isOpen={isPanelOpen} ref={panelWrapRef}>
                    {category.productConnection.productFilterOptions !== null && (
                        <Filter
                            key={category.slug}
                            productFilterOptions={category.productConnection.productFilterOptions}
                            slug={category.slug}
                            originalSlug={category.originalCategorySlug}
                            orderingMode={category.productConnection.orderingMode}
                            defaultOrderingMode={category.productConnection.defaultOrderingMode}
                        />
                    )}
                    <Overlay isHiddenOnDesktop onClick={handlePanelOpenerClick} />
                </CategoryDetailPanelStyled>
                <CategoryDetailContentStyled>
                    <CategoryDetailAdvertsStyled positionName="productList" />
                    <Heading type={'h1'}>{category.seoH1 !== null ? category.seoH1 : category.name}</Heading>
                    {category.description !== null && category.description !== '' && (query.page ?? 1) === 1 && (
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
                        productFilterOptions={category.productConnection.productFilterOptions!}
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
