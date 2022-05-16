import HamburgerIcon from './HamburgerIcon';
import { HamburgerMenuImageStyled, HamburgerMenuStyled, HamburgerMenuTextStyled } from './HamburgerMenu.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, MouseEventHandler } from 'react';

type HamburgerMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: MouseEventHandler<HTMLDivElement>;
};

const HamburgerMenu: FC<HamburgerMenuProps> = (props) => {
    const testIdentifier = 'layout-header-hamburgermenu';

    const t = useTypedTranslationFunction();

    return (
        <HamburgerMenuStyled
            onClick={props.onMenuToggleHandler}
            isOpen={props.isMenuOpened}
            data-testid={testIdentifier}
        >
            <HamburgerMenuImageStyled>
                <HamburgerIcon isMenuOpened={props.isMenuOpened} />
            </HamburgerMenuImageStyled>
            <HamburgerMenuTextStyled>{props.isMenuOpened ? t('Close') : t('Menu')}</HamburgerMenuTextStyled>
        </HamburgerMenuStyled>
    );
};

/* @component */
export default HamburgerMenu;
