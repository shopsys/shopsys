import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { CategoryCard } from './CategoryCard';

type PromotedCategoriesContentProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery;
};

export const PromotedCategoriesContent: FC<PromotedCategoriesContentProps> = ({ promotedCategoriesData }) => {
    const categoriesLength = promotedCategoriesData.promotedCategories.length;

    return (
        <ul
            className={twMergeCustom(
                'hide-scrollbar vl:overflow-visible overflow-x-auto overflow-y-hidden overscroll-x-contain',
                'grid auto-cols-[150px] vl:grid-flow-row grid-flow-col vl:grid-cols-4 gap-3 vl:gap-5 lg:auto-cols-[200px]',
                categoriesLength > 4 ? 'vl:grid-rows-2' : 'vl:grid-rows-1',
            )}
        >
            {promotedCategoriesData.promotedCategories.map((category, index) => {
                const isFirstItemLarge = categoriesLength > 4 && index === 0;

                return (
                    <li
                        key={category.uuid}
                        className={twJoin(index === 0 && categoriesLength > 4 && 'vl:col-span-2 vl:row-span-2')}
                    >
                        <CategoryCard
                            category={category}
                            size={isFirstItemLarge ? 'large' : 'default'}
                            variant="homepage"
                        />
                    </li>
                );
            })}
        </ul>
    );
};
