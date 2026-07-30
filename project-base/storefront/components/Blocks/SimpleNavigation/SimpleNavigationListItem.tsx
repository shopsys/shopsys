import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { ListedItemPropType } from 'types/simpleNavigation';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { twMergeCustom } from 'utils/twMerge';
import { getLinkType } from './simpleNavigationUtils';

type SimpleNavigationListItemProps = {
    listedItem: ListedItemPropType;
    imageType?: string;
    linkTypeOverride?: PageType;
};

export const SimpleNavigationListItem: FC<SimpleNavigationListItemProps> = ({ listedItem, linkTypeOverride, tid }) => {
    const { t } = useTranslation();
    const itemImage = 'mainImage' in listedItem ? listedItem.mainImage : null;
    const icon = 'icon' in listedItem ? listedItem.icon : null;
    const href = `${getStringWithoutTrailingSlash(listedItem.slug)}/`;
    const linkType = linkTypeOverride ?? getLinkType(listedItem.__typename);

    return (
        <ExtendedNextLink
            aria-label={t('Go to category {{ categoryName }}', { ns: 'accessibility', categoryName: listedItem.name })}
            data-tid={tid}
            href={href}
            title={t('Go to category')}
            type={linkType}
            className={twMergeCustom(
                'relative flex h-full w-full cursor-pointer items-center gap-5 rounded-xl border border-background-more bg-background-more px-5 py-2.5 no-underline transition-[box-shadow,border-color,background-color,color] duration-200 ease-out',
                'text-text-default hover:border-border-less hover:bg-background-default hover:text-text-default hover:no-underline',
                'pointer-fine:hover:shadow-[0_12px_24px_-18px_rgb(37_40_61/40%),0_4px_10px_-8px_rgb(37_40_61/24%)] motion-reduce:duration-0',
                'lg:justify-start lg:gap-3 lg:px-3 lg:py-2',
            )}
        >
            {itemImage && (
                <div className="shrink-0" data-tid={TIDs.simple_navigation_image}>
                    <Image
                        priority
                        alt={itemImage.name || listedItem.name}
                        className="size-15 object-contain mix-blend-multiply"
                        height={60}
                        src={itemImage.url}
                        width={60}
                    />
                </div>
            )}

            {icon}

            <div className="z-above font-semibold text-sm">{listedItem.name}</div>
            {'totalCount' in listedItem && listedItem.totalCount !== undefined && (
                <span className="ml-2 whitespace-nowrap text-sm">({listedItem.totalCount})</span>
            )}
        </ExtendedNextLink>
    );
};
