import { getLinkType } from './simpleNavigationUtils';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropType } from 'types/simpleNavigation';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { twMergeCustom } from 'utils/twMerge';

type SimpleNavigationListItemProps = {
    listedItem: ListedItemPropType;
    imageType?: string;
    linkTypeOverride?: PageType;
};

export const SimpleNavigationListItem: FC<SimpleNavigationListItemProps> = ({
    listedItem,
    linkTypeOverride,
    tid,
    className,
}) => {
    const itemImage = 'mainImage' in listedItem ? listedItem.mainImage : null;
    const icon = 'icon' in listedItem ? listedItem.icon : null;
    const href = getStringWithoutTrailingSlash(listedItem.slug) + '/';
    const linkType = linkTypeOverride ?? getLinkType(listedItem.__typename);

    return (
        <li tid={tid}>
            <ExtendedNextLink
                href={href}
                type={linkType}
                className={twMergeCustom(
                    'flex h-full w-full cursor-pointer items-center gap-5 rounded-xl px-5 py-2.5 no-underline transition lg:justify-start lg:gap-3 lg:px-3 lg:py-2',
                    className,
                    'bg-backgroundMore text-text',
                    'hover:bg-backgroundMost hover:text-text hover:no-underline',
                )}
            >
                {itemImage && (
                    <div className="shrink-0" tid={TIDs.simple_navigation_image}>
                        <Image
                            alt={itemImage.name || listedItem.name}
                            className="mix-blend-multiply max-h-14 h-auto max-w-14 w-auto"
                            height={56}
                            src={itemImage.url}
                            width={56}
                        />
                    </div>
                )}

                {icon}

                <div className="text-sm font-semibold">{listedItem.name}</div>
                {'totalCount' in listedItem && listedItem.totalCount !== undefined && (
                    <span className="ml-2 whitespace-nowrap text-sm">({listedItem.totalCount})</span>
                )}
            </ExtendedNextLink>
        </li>
    );
};
