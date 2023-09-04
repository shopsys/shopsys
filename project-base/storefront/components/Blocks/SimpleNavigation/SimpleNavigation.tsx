import { SimpleNavigationListItem } from './SimpleNavigationListItem';
import { getSearchResultLinkType } from 'helpers/mappers/simpleNavigation';
import { ListedItemPropType } from 'types/simpleNavigation';
import { twMergeCustom } from 'helpers/twMerge';

type SimpleNavigationProps = {
    listedItems: ListedItemPropType[];
    imageType?: string;
    isWithoutSlider?: true;
};

const TEST_IDENTIFIER = 'blocks-simplenavigation';

export const SimpleNavigation: FC<SimpleNavigationProps> = ({ listedItems, imageType, isWithoutSlider, className }) => {
    return (
        <ul
            className={twMergeCustom(
                !isWithoutSlider &&
                    'snap-x snap-mandatory auto-cols-[40%] grid-flow-col overflow-x-auto overflow-y-hidden overscroll-x-contain lg:grid-flow-row',
                'grid gap-3 lg:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]',
                className,
            )}
            data-testid={TEST_IDENTIFIER}
        >
            {listedItems.map((listedItem, index) => (
                <SimpleNavigationListItem
                    key={index}
                    linkType={getSearchResultLinkType(listedItem)}
                    listedItem={listedItem}
                    imageType={imageType}
                    dataTestId={TEST_IDENTIFIER + '-' + index}
                >
                    {listedItem.name}
                </SimpleNavigationListItem>
            ))}
        </ul>
    );
};
