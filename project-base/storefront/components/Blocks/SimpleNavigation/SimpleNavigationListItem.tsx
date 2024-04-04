import { getLinkType } from './utils';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';
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
    const href = getStringWithoutTrailingSlash(listedItem.slug) + '/';
    const linkType = linkTypeOverride ?? getLinkType(listedItem.__typename);

    return (
        <li tid={tid}>
            <ExtendedNextLink
                href={href}
                type={linkType}
                className={twMergeCustom(
                    'flex h-full w-full cursor-pointer flex-col items-center justify-center gap-2 rounded bg-greyVeryLight px-2 py-4 no-underline transition hover:bg-whitesmoke hover:no-underline lg:flex-row lg:justify-start lg:gap-3 lg:px-3 lg:py-2',
                    className,
                )}
            >
                {itemImage && (
                    <div className="h-12 w-16 shrink-0">
                        <Image
                            alt={itemImage.name || listedItem.name}
                            className="mx-auto max-h-full w-auto mix-blend-multiply"
                            height={48}
                            src={itemImage.url}
                            width={64}
                        />
                    </div>
                )}

                <div className={twJoin('text-center ', itemImage && 'lg:text-left')}>
                    <div className="text-sm text-dark">{listedItem.name}</div>
                    {'totalCount' in listedItem && listedItem.totalCount !== undefined && (
                        <span className="ml-2 whitespace-nowrap text-sm text-greyLight">({listedItem.totalCount})</span>
                    )}
                </div>
            </ExtendedNextLink>
        </li>
    );
};
