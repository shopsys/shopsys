import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { ListedItemPropType } from 'types/simpleNavigation';

type ListItemProps = {
    listedItem: ListedItemPropType;
    imageType?: string;
    linkType: FriendlyPagesTypesKeys | 'static';
};

const TEST_IDENTIFIER = 'blocks-simplenavigation-listitem';

export const ListItem: FC<ListItemProps> = ({ listedItem, imageType, linkType }) => {
    const itemImage = 'mainImage' in listedItem ? listedItem.mainImage : null;

    return (
        <ExtendedNextLink
            type={linkType}
            href={listedItem.slug}
            className="flex h-full w-full cursor-pointer flex-col items-center rounded-xl bg-greyVeryLight px-2 py-4 no-underline transition hover:bg-whitesmoke hover:no-underline lg:flex-row lg:px-3 lg:py-2"
            data-testid={TEST_IDENTIFIER}
        >
            <>
                {itemImage && (
                    <div className="relative mb-1 h-12 w-16 flex-shrink-0 flex-grow-0 basis-auto lg:mb-0">
                        <Image
                            className="!max-h-full mix-blend-multiply"
                            image={itemImage}
                            type={imageType ?? 'default'}
                            alt={itemImage.name || listedItem.name}
                        />
                    </div>
                )}
                <div className="max-w-full">
                    <span className="block max-w-full text-sm text-dark lg:pl-3">{listedItem.name}</span>
                    {'totalCount' in listedItem && listedItem.totalCount !== undefined && (
                        <span className="ml-2 whitespace-nowrap text-sm text-greyLight">({listedItem.totalCount})</span>
                    )}
                </div>
            </>
        </ExtendedNextLink>
    );
};
