import { HamburgerIcon } from './HamburgerIcon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';

type HamburgerMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: MouseEventHandler<HTMLDivElement>;
};

const TEST_IDENTIFIER = 'layout-header-hamburgermenu';

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ isMenuOpened, onMenuToggleHandler }) => {
    const t = useTypedTranslationFunction();

    return (
        <div
            className={twJoin(
                'flex h-10 w-full cursor-pointer items-center rounded bg-orangeLight p-3',
                isMenuOpened && 'z-aboveMobileMenu',
            )}
            onClick={onMenuToggleHandler}
            data-testid={TEST_IDENTIFIER}
        >
            <div className="flex w-4 items-center justify-center">
                <HamburgerIcon isMenuOpened={isMenuOpened} />
            </div>
            <span className="ml-1 w-7 text-xs">{isMenuOpened ? t('Close') : t('Menu')}</span>
        </div>
    );
};
