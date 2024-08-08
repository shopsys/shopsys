import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';

type HamburgerMenuProps = {
    onClick: MouseEventHandler<HTMLDivElement> | undefined;
};

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ onClick }) => {
    return (
        <div
            className={twJoin('flex h-10 w-8 cursor-pointer items-center rounded bg-none text-linkInverted')}
            onClick={onClick}
        >
            <div className="flex w-7 items-center justify-center">
                <MenuIcon className="w-7" />
            </div>
        </div>
    );
};
