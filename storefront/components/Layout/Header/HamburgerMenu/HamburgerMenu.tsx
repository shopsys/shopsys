import HamburgerIcon from './HamburgerIcon';
import { HamburgerMenuImageStyled, HamburgerMenuStyled, HamburgerMenuTextStyled } from './HamburgerMenu.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, MouseEventHandler } from 'react';

type HamburgerMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: MouseEventHandler<HTMLDivElement>;
};

const TEST_IDENTIFIER = 'layout-header-hamburgermenu';

const HamburgerMenu: FC<HamburgerMenuProps> = ({ isMenuOpened, onMenuToggleHandler }) => {
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

/* @component */
export default HamburgerMenu;
