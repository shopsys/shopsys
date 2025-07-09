import { MenuItem } from './MobileMenuContent';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import useTranslation from 'next-translate/useTranslation';

type DropdownMenuListProps = {
    navigationItem: MenuItem;
    onExpand: () => void;
    onNavigate: () => void;
};

export const DropdownMenuListItem: FC<DropdownMenuListProps> = ({ navigationItem, onExpand, onNavigate }) => {
    const { t } = useTranslation();
    const isWithChildren = !!navigationItem.children?.length;

    return (
        <div key={navigationItem.link + navigationItem.name} className="flex">
            <ExtendedNextLink
                className="text-text-default flex-1 py-4 font-bold no-underline"
                href={navigationItem.link}
                skeletonType={DEFAULT_SKELETON_TYPE}
                onClick={onNavigate}
            >
                {navigationItem.name}
            </ExtendedNextLink>

            {isWithChildren && (
                <button
                    className="text-text-default flex w-10 cursor-pointer items-center justify-end"
                    tabIndex={0}
                    title={t('Expand menu')}
                    type="button"
                    onClick={onExpand}
                >
                    <ArrowIcon className="size-5 -rotate-90" />
                </button>
            )}
        </div>
    );
};
