import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
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
                className="flex w-full cursor-pointer items-center justify-between rounded-none py-4 text-left font-secondary font-semibold text-base text-text-default"
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
                className="inline-flex w-full items-center justify-center gap-1.5 rounded-sm py-5 font-secondary font-semibold text-link-default text-sm no-underline"
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
            className="block rounded-none py-4 font-secondary font-semibold text-base text-text-default no-underline"
            href={navigationItem.link}
            skeletonType={DEFAULT_SKELETON_TYPE}
            tabIndex={isHidden ? -1 : undefined}
            onClick={onNavigate}
        >
            {navigationItem.name}
        </ExtendedNextLink>
    );
};
