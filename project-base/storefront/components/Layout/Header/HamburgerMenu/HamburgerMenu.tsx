import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';

type HamburgerMenuProps = {
    onClick: MouseEventHandler<HTMLDivElement> | undefined;
};

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ onClick }) => {
    return (
        <div
            className={twJoin('text-link-inverted-default flex cursor-pointer items-center rounded-sm bg-none')}
            onClick={onClick}
        >
            <div className="flex items-center justify-center">
                <MenuIcon className="size-6" />
            </div>
        </div>
    );
};
