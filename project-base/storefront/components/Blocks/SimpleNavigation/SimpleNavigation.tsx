import { ListItem } from './ListItem';
import { getSearchResultLinkType } from 'helpers/mappers/simpleNavigation';
import { ListedItemPropType } from 'types/simpleNavigation';
import { twMergeCustom } from 'helpers/twMerge';

type SimpleNavigationProps = {
    listedItems: ListedItemPropType[];
    imageType?: string;
};

const TEST_IDENTIFIER = 'blocks-simplenavigation';

export const SimpleNavigation: FC<SimpleNavigationProps> = ({ listedItems, imageType, className }) => {
    return (
        <ul
            className={twMergeCustom(
                'grid snap-x snap-mandatory auto-cols-[40%] gap-3 overflow-x-auto overscroll-x-contain max-lg:grid-flow-col lg:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]',
                className,
            )}
            data-testid={TEST_IDENTIFIER}
        >
            {listedItems.map((listedItem, index) => (
                <ListItem
                    key={index}
                    linkType={getSearchResultLinkType(listedItem)}
                    listedItem={listedItem}
                    imageType={imageType}
                    dataTestId={TEST_IDENTIFIER + '-' + index}
                >
                    {listedItem.name}
                </ListItem>
            ))}
        </ul>
    );
};
