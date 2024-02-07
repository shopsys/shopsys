import { SimpleNavigationListItem } from './SimpleNavigationListItem';
import { DataTestIds } from 'cypress/dataTestIds';
import { twMergeCustom } from 'helpers/twMerge';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropType } from 'types/simpleNavigation';

type SimpleNavigationProps = {
    listedItems: ListedItemPropType[];
    isWithoutSlider?: true;
    itemClassName?: string;
    linkType: PageType;
};

export const SimpleNavigation: FC<SimpleNavigationProps> = ({
    listedItems,
    isWithoutSlider,
    className,
    itemClassName,
    linkType,
}) => {
    return (
        <ul
            className={twMergeCustom(
                !isWithoutSlider &&
                    'snap-x snap-mandatory auto-cols-[40%] grid-flow-col overflow-x-auto overflow-y-hidden overscroll-x-contain lg:grid-flow-row',
                'grid gap-3 lg:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]',
                className,
            )}
        >
            {listedItems.map((listedItem, index) => (
                <SimpleNavigationListItem
                    key={index}
                    className={itemClassName}
                    dataTestId={DataTestIds.blocks_simplenavigation_ + index}
                    linkType={linkType}
                    listedItem={listedItem}
                >
                    {listedItem.name}
                </SimpleNavigationListItem>
            ))}
        </ul>
    );
};
