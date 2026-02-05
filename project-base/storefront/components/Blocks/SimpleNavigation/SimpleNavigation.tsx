import { SimpleNavigationListItem } from './SimpleNavigationListItem';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropType } from 'types/simpleNavigation';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type SimpleNavigationProps = {
    title?: string;
    listedItems: ListedItemPropType[];
    isWithoutSlider?: true;
    itemClassName?: string;
    linkTypeOverride?: PageType;
    ariaLabel?: string;
};

export const SimpleNavigation: FC<SimpleNavigationProps> = ({
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
                aria-label={ariaLabel || title || t('Navigation', { ns: 'accessibility' })}
                className={twMergeCustom(
                    !isWithoutSlider &&
                        'snap-x snap-mandatory auto-cols-[40%] grid-flow-col overflow-x-auto overflow-y-hidden overscroll-x-contain md:grid-flow-row',
                    'grid gap-3 md:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]',
                    className,
                )}
            >
                {listedItems.map((listedItem, index) => (
                    <SimpleNavigationListItem
                        key={listedItem.slug}
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
