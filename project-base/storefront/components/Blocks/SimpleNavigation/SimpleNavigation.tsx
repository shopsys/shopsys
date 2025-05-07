import { SimpleNavigationListItem } from './SimpleNavigationListItem';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { memo } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropType } from 'types/simpleNavigation';
import { twMergeCustom } from 'utils/twMerge';

type SimpleNavigationProps = {
    title?: string;
    listedItems: ListedItemPropType[];
    isWithoutSlider?: true;
    itemClassName?: string;
    linkTypeOverride?: PageType;
    ariaLabel?: string;
};

const SimpleNavigationComp: FC<SimpleNavigationProps> = ({
    title,
    listedItems,
    isWithoutSlider,
    className,
    itemClassName,
    linkTypeOverride,
    ariaLabel,
}) => {
    const { t } = useTranslation();
    if (listedItems.length === 0) {
        return null;
    }

    return (
        <Webline>
            {title && <h2 className="sr-only">{title}</h2>}
            <nav
                aria-label={ariaLabel || title || t('Navigation')}
                className={twMergeCustom(
                    !isWithoutSlider &&
                        'snap-x snap-mandatory auto-cols-[40%] grid-flow-col overflow-x-auto overflow-y-hidden overscroll-x-contain md:grid-flow-row',
                    'grid gap-3 md:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]',
                    className,
                )}
            >
                {listedItems.map((listedItem, index) => (
                    <SimpleNavigationListItem
                        key={index}
                        className={itemClassName}
                        linkTypeOverride={linkTypeOverride}
                        listedItem={listedItem}
                        tid={TIDs.blocks_simplenavigation_ + index}
                    >
                        {listedItem.name}
                    </SimpleNavigationListItem>
                ))}
            </nav>
        </Webline>
    );
};

export const SimpleNavigation = memo(SimpleNavigationComp);
