import { HamburgerIcon } from './HamburgerIcon/HamburgerIcon';
import { HamburgerMenuImageStyled, HamburgerMenuStyled, HamburgerMenuTextStyled } from './HamburgerMenu.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, MouseEventHandler } from 'react';

type HamburgerMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: MouseEventHandler<HTMLDivElement>;
};

const TEST_IDENTIFIER = 'layout-header-hamburgermenu';

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ isMenuOpened, onMenuToggleHandler }) => {
    const t = useTypedTranslationFunction();

    return (
        <HamburgerMenuStyled onClick={onMenuToggleHandler} isOpen={isMenuOpened} data-testid={TEST_IDENTIFIER}>
            <HamburgerMenuImageStyled>
                <HamburgerIcon isMenuOpened={isMenuOpened} />
            </HamburgerMenuImageStyled>
            <HamburgerMenuTextStyled>{isMenuOpened ? t('Close') : t('Menu')}</HamburgerMenuTextStyled>
        </HamburgerMenuStyled>
    );
};
