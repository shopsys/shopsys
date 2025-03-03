import { MenuItem } from './MobileMenuContent';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';

type DropdownMenuListProps = {
    navigationItem: MenuItem;
    onExpand: () => void;
    onNavigate: () => void;
};

export const DropdownMenuListItem: FC<DropdownMenuListProps> = ({ navigationItem, onExpand, onNavigate }) => {
    const isWithChildren = !!navigationItem.children?.length;

    return (
        <div key={navigationItem.link + navigationItem.name} className="flex py-3">
            <ExtendedNextLink
                className="text-text flex-1 font-bold uppercase no-underline"
                href={navigationItem.link}
                skeletonType={DEFAULT_SKELETON_TYPE}
                onClick={onNavigate}
            >
                {navigationItem.name}
            </ExtendedNextLink>

            {isWithChildren && (
                <span className="text-text flex w-11 cursor-pointer items-center justify-center" onClick={onExpand}>
                    <ArrowIcon className="size-5 -rotate-90" />
                </span>
            )}
        </div>
    );
};
