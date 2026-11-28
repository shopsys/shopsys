import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { getLinkClassName } from 'components/Basic/Link/linkUtils';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { MenuItem } from './MobileMenuContent';

type DropdownMenuListProps = {
    isHidden: boolean;
    navigationItem: MenuItem;
    onExpand: () => void;
    onNavigate: () => void;
};

export const DropdownMenuListItem: FC<DropdownMenuListProps> = ({ isHidden, navigationItem, onExpand, onNavigate }) => {
    const { t } = useTranslation();
    const isWithChildren = !!navigationItem.children?.length;

    if (isWithChildren) {
        return (
            <button
                aria-expanded="false"
                className="flex min-h-12 w-full cursor-pointer items-center justify-between rounded-control bg-background-more px-3 py-3 text-left font-ui text-base text-text-default transition-colors hover:bg-background-most active:bg-background-most"
                tabIndex={isHidden ? -1 : 0}
                title={t('Open submenu')}
                type="button"
                onClick={onExpand}
            >
                <span>{navigationItem.name}</span>
                <ArrowIcon className="size-5 -rotate-90" />
            </button>
        );
    }

    if (navigationItem.link === null) {
        return null;
    }

    if (navigationItem.isViewAllLink) {
        return (
            <ExtendedNextLink
                className={twJoin(
                    getLinkClassName({ variant: 'navigation', tone: 'accent' }),
                    'mt-2 inline-flex w-full items-center gap-1.5 rounded-none border-border-less border-t px-2 pt-4 font-semibold text-sm',
                )}
                href={navigationItem.link}
                skeletonType={DEFAULT_SKELETON_TYPE}
                tabIndex={isHidden ? -1 : undefined}
                onClick={onNavigate}
            >
                <span>{navigationItem.name}</span>
                <ArrowIcon className="size-4 -rotate-90" />
            </ExtendedNextLink>
        );
    }

    return (
        <ExtendedNextLink
            className="flex min-h-12 w-full items-center rounded-control bg-background-more px-3 py-3 font-ui text-base text-text-default no-underline transition-colors hover:bg-background-most hover:text-text-default hover:no-underline active:bg-background-most"
            href={navigationItem.link}
            skeletonType={DEFAULT_SKELETON_TYPE}
            tabIndex={isHidden ? -1 : undefined}
            onClick={onNavigate}
        >
            {navigationItem.name}
        </ExtendedNextLink>
    );
};
