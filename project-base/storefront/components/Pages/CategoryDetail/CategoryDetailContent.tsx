import { Heading } from 'components/Basic/Heading/Heading';
import { AdvancedSeoCategories } from './AdvancedSeoCategories';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useCallback, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { Filter } from 'components/Basic/Icon/IconsSvg';
import { CategoryBestsellers } from './CategoryBestsellers/CategoryBestsellers';

type CategoryDetailContentProps = {
    category: CategoryDetailFragmentApi;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category }) => {
    const { t } = useTranslation();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { query } = useRouter();
    const isFiltered = 'filter' in query;

    const title = useSeoTitleWithPagination(category.products.totalCount, category.name, category.seoH1);

    const handlePanelOpenerClick = useCallback(() => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    }, []);

    return (
        <Webline>
            {isFiltered && <MetaRobots content="noindex, follow" />}
            <div
                className="mb-7 flex scroll-mt-5 flex-col vl:mb-10 vl:flex-row vl:flex-wrap"
                ref={paginationScrollTargetRef}
            >
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-[400px] -translate-x-full transition max-vl:z-aboveOverlay vl:static vl:w-[304px] vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'translate-x-0',
                    )}
                >
                    <FilterPanel
                        productFilterOptions={category.products.productFilterOptions}
                        orderingMode={category.products.orderingMode}
                        defaultOrderingMode={category.products.defaultOrderingMode}
                        originalSlug={category.originalCategorySlug}
                        panelCloseHandler={handlePanelOpenerClick}
                        slug={category.slug}
                        totalCount={category.products.totalCount}
                    />
                </div>
                <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />
                <div className="flex flex-1 flex-col vl:pl-12">
                    <Adverts positionName="productList" className="mb-5" />
                    <Heading type="h1">{title}</Heading>
                    {category.description !== null &&
                        category.description !== '' &&
                        (query[PAGE_QUERY_PARAMETER_NAME] ?? 1) === 1 && (
                            <div dangerouslySetInnerHTML={{ __html: category.description }} className="mb-4" />
                        )}
                    <Adverts positionName="productListMiddle" currentCategory={category} className="mb-7" />
                    <SimpleNavigation
                        listedItems={[...category.children, ...category.linkedCategories]}
                        className="mb-6"
                    />
                    <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                    {category.bestsellers.length > 0 && <CategoryBestsellers products={category.bestsellers} />}
                    <div className="flex flex-col gap-3 sm:flex-row">
                        <div
                            className="relative flex w-full cursor-pointer flex-row items-center justify-center rounded bg-primary py-3 px-8 font-bold uppercase text-white vl:mb-3 vl:hidden"
                            onClick={handlePanelOpenerClick}
                        >
                            <Icon icon={<Filter />} className="mr-3 w-6 font-bold text-white" />
                            {t('Filter')}
                        </div>
                        <SortingBar
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                        />
                    </div>
                    <CategoryDetailProductsWrapper
                        category={category}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                    />
                </div>
            </div>
        </Webline>
    );
};
