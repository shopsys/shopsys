import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { getStringWithoutTrailingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { twMergeCustom } from 'helpers/twMerge';
import { twJoin } from 'tailwind-merge';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { ListedItemPropType } from 'types/simpleNavigation';

type SimpleNavigationListItemProps = {
    listedItem: ListedItemPropType;
    imageType?: string;
    linkType: FriendlyPagesTypesKeys | 'static';
};

export const SimpleNavigationListItem: FC<SimpleNavigationListItemProps> = ({
    listedItem,
    imageType,
    linkType,
    dataTestId,
    className,
}) => {
    const itemImage = 'mainImage' in listedItem ? listedItem.mainImage : null;
    const href = getStringWithoutTrailingSlash(listedItem.slug) + '/';

    return (
        <li data-testid={dataTestId}>
            <ExtendedNextLink
                href={href}
                type={linkType}
                className={twMergeCustom(
                    'flex h-full w-full cursor-pointer flex-col items-center justify-center gap-2 rounded bg-greyVeryLight px-2 py-4 no-underline transition hover:bg-whitesmoke hover:no-underline lg:flex-row lg:justify-start lg:gap-3 lg:px-3 lg:py-2',
                    className,
                )}
            >
                {itemImage && (
                    <Image
                        alt={itemImage.name || listedItem.name}
                        className="h-12 min-w-[64px] mix-blend-multiply"
                        image={itemImage}
                        type={imageType ?? 'default'}
                        width={64}
                    />
                )}

                <div className={twJoin('max-w-full text-center ', itemImage && 'lg:text-left')}>
                    <span className="block max-w-full text-sm text-dark">{listedItem.name}</span>
                    {'totalCount' in listedItem && listedItem.totalCount !== undefined && (
                        <span className="ml-2 whitespace-nowrap text-sm text-greyLight">({listedItem.totalCount})</span>
                    )}
                </div>
            </ExtendedNextLink>
        </li>
    );
};
