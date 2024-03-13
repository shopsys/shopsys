import { AdvancedSeoCategories } from './AdvancedSeoCategories';
import { CategoryBestsellers } from './CategoryBestsellers/CategoryBestsellers';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { FilterIcon } from 'components/Basic/Icon/IconsSvg';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type CategoryDetailContentProps = {
    category: CategoryDetailFragmentApi;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category }) => {
    const { t } = useTranslation();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { currentPage } = useQueryParams();

    const title = useSeoTitleWithPagination(category.products.totalCount, category.name, category.seoH1);

    const handlePanelOpenerClick = () => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    };

    return (
        <Webline>
            <div
                className="mb-7 flex scroll-mt-5 flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-12"
                ref={paginationScrollTargetRef}
            >
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-[400px] -translate-x-full transition max-vl:z-aboveOverlay vl:static vl:w-[304px] vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'translate-x-0',
                    )}
                >
                    <FilterPanel
                        defaultOrderingMode={category.products.defaultOrderingMode}
                        orderingMode={category.products.orderingMode}
                        originalSlug={category.originalCategorySlug}
                        panelCloseHandler={handlePanelOpenerClick}
                        productFilterOptions={category.products.productFilterOptions}
                        slug={category.slug}
                        totalCount={category.products.totalCount}
                    />
                </div>

                <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />

                <div className="flex flex-1 flex-col">
                    <Adverts className="mt-6" currentCategory={category} positionName="productList" />

                    <h1 className="mb-3">{title}</h1>

                    {!!category.description && currentPage === 1 && (
                        <div dangerouslySetInnerHTML={{ __html: category.description }} />
                    )}

                    <Adverts className="mt-6" currentCategory={category} positionName="productListMiddle" />

                    <SimpleNavigation
                        className="mt-6"
                        linkTypeOverride="category"
                        listedItems={[...category.children, ...category.linkedCategories]}
                    />

                    {!!category.readyCategorySeoMixLinks.length && (
                        <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                    )}

                    {!!category.bestsellers.length && <CategoryBestsellers products={category.bestsellers} />}

                    <div className="mt-6 flex flex-col items-stretch gap-3 sm:flex-row">
                        <div
                            className="relative flex flex-1 cursor-pointer items-center justify-center rounded bg-primary p-3 font-bold uppercase text-white vl:mb-3 vl:hidden"
                            onClick={handlePanelOpenerClick}
                        >
                            <FilterIcon className="mr-3 w-6 font-bold text-white" />
                            {t('Filter')}
                        </div>

                        <SortingBar
                            className="flex-1"
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
