import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';

type HamburgerMenuProps = {
    onClick: MouseEventHandler<HTMLDivElement> | undefined;
};

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ onClick }) => {
    return (
        <div className={twJoin('flex cursor-pointer items-center rounded bg-none text-linkInverted')} onClick={onClick}>
            <div className="flex items-center justify-center">
                <MenuIcon className="size-6" />
            </div>
        </div>
    );
};
