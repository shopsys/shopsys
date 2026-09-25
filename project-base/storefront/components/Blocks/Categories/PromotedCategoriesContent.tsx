import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { GtmPromotionType } from 'gtm/types/events';
import { useGtmPromotionListViewEvent } from 'gtm/utils/pageReadyEvents/useGtmPromotionListViewEvent';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { CategoryCard } from './CategoryCard';

type PromotedCategoriesContentProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery;
};

const MAX_VISIBLE_PROMOTED_CATEGORIES = 9;

export const PromotedCategoriesContent: FC<PromotedCategoriesContentProps> = ({ promotedCategoriesData }) => {
    const visiblePromotedCategories = promotedCategoriesData.promotedCategories.slice(
        0,
        MAX_VISIBLE_PROMOTED_CATEGORIES,
    );
    const hasFeaturedCategory = visiblePromotedCategories.length > 4;
    const promotions: GtmPromotionType[] = visiblePromotedCategories.map((category) => ({
        promotionId: 'homepage_promoted_categories',
        promotionName: 'Homepage - recommended categories',
        creativeName: category.name,
        creativeSlot: category.id,
    }));

    useGtmPromotionListViewEvent(promotions);

    return (
        <ul
            className={twMergeCustom(
                'hide-scrollbar vl:overflow-visible overflow-x-auto overflow-y-hidden overscroll-x-contain',
                'grid auto-cols-[150px] vl:grid-flow-row grid-flow-col vl:grid-cols-5 gap-3 vl:gap-5 lg:auto-cols-[200px]',
                hasFeaturedCategory ? 'vl:h-75 vl:grid-rows-2 xl:h-85' : 'vl:h-40 vl:grid-rows-1',
            )}
        >
            {visiblePromotedCategories.map((category, index) => {
                const isFirstItemLarge = hasFeaturedCategory && index === 0;

                return (
                    <li key={category.uuid} className={twJoin('group', isFirstItemLarge && 'vl:row-span-2')}>
                        <CategoryCard
                            category={category}
                            promotion={promotions[index]}
                            size={isFirstItemLarge ? 'large' : 'default'}
                            variant="homepage"
                        />
                    </li>
                );
            })}
        </ul>
    );
};
