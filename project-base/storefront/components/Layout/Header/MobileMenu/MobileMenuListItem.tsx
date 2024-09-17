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
        <div key={navigationItem.link} className="flex border-b border-borderAccent py-3 last:border-b-0">
            <ExtendedNextLink
                className="flex-1 font-bold uppercase text-text no-underline"
                href={navigationItem.link}
                skeletonType={DEFAULT_SKELETON_TYPE}
                onClick={onNavigate}
            >
                {navigationItem.name}
            </ExtendedNextLink>

            {isWithChildren && (
                <span className="flex w-11 cursor-pointer items-center justify-center text-text" onClick={onExpand}>
                    <ArrowIcon className="w-5 -rotate-90" />
                </span>
            )}
        </div>
    );
};
