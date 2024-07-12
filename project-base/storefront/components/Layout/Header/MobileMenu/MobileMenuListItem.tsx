import { MenuItem } from './MobileMenuContent';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';

type DropdownMenuListProps = {
    navigationItem: MenuItem;
    onExpand: () => void;
    onNavigate: () => void;
};

export const DropdownMenuListItem: FC<DropdownMenuListProps> = ({ navigationItem, onExpand, onNavigate }) => {
    const isWithChildren = !!navigationItem.children?.length;

    return (
        <div key={navigationItem.link} className="flex py-3 border-b border-graySlate last:border-b-0">
            <ExtendedNextLink
                className="flex-1 font-bold text-dark no-underline uppercase"
                href={navigationItem.link}
                onClick={onNavigate}
            >
                {navigationItem.name}
            </ExtendedNextLink>

            {isWithChildren && (
                <span className="flex w-11 cursor-pointer items-center justify-center text-dark" onClick={onExpand}>
                    <ArrowIcon className="w-5 -rotate-90" />
                </span>
            )}
        </div>
    );
};
