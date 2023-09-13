import { Heading } from 'components/Basic/Heading/Heading';
import { AdvancedSeoCategories } from './AdvancedSeoCategories';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { CategoryBestsellers } from './CategoryBestsellers/CategoryBestsellers';
import { FilterIcon } from 'components/Basic/Icon/IconsSvg';
import dynamic from 'next/dynamic';
import { useQueryParams } from 'hooks/useQueryParams';

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

                    {!!category.description && currentPage === 1 && (
                        <div dangerouslySetInnerHTML={{ __html: category.description }} className="mb-4" />
                    )}

                    <Adverts positionName="productListMiddle" currentCategory={category} className="mb-7" />

                    <SimpleNavigation
                        listedItems={[...category.children, ...category.linkedCategories]}
                        className="mb-6"
                    />

                    {!!category.readyCategorySeoMixLinks.length && (
                        <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                    )}

                    {!!category.bestsellers.length && <CategoryBestsellers products={category.bestsellers} />}

                    <div className="flex flex-col items-stretch gap-3 sm:flex-row">
                        <div
                            className="relative flex flex-1 cursor-pointer items-center justify-center rounded bg-primary p-3 font-bold uppercase text-white vl:mb-3 vl:hidden"
                            onClick={handlePanelOpenerClick}
                        >
                            <FilterIcon className="mr-3 w-6 font-bold text-white" />
                            {t('Filter')}
                        </div>

                        <SortingBar
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                            className="flex-1"
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
