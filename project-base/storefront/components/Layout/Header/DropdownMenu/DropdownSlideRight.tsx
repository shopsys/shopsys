import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu/DropdownMenuContext';
import { useContext } from 'react';
import { DropdownItemType } from 'types/dropdown';

export const DropdownSlideRight: FC<DropdownItemType> = (dropdownItemProps) => {
    const context = useContext(DropdownMenuContext);

    return (
        <span
            className="flex w-11 cursor-pointer items-center justify-center text-dark"
            onClick={() => context.slideRight(dropdownItemProps)}
        >
            <ArrowIcon className="w-4 -rotate-90" />
        </span>
    );
};
