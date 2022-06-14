import { createContext } from 'react';
import { DropdownItemType } from 'types/dropdown';

export const DropdownMenuContext = createContext<{
    slideRight: (props: DropdownItemType) => void;
    onMenuToggleHandler: () => void;
}>({
    slideRight: () => undefined,
    onMenuToggleHandler: () => undefined,
});
