import { FC, MouseEventHandler } from 'react';
import { HamburgerMenuImageStyled, HamburgerMenuStyled, HamburgerMenuTextStyled } from './HamburgerMenu.style';
import HamburgerIcon from './HamburgerIcon';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type HamburgerMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: MouseEventHandler<HTMLDivElement>;
};

const HamburgerMenu: FC<HamburgerMenuProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <HamburgerMenuStyled onClick={props.onMenuToggleHandler}>
            <HamburgerMenuImageStyled>
                <HamburgerIcon isMenuOpened={props.isMenuOpened} />
            </HamburgerMenuImageStyled>
            <HamburgerMenuTextStyled>{props.isMenuOpened ? t('Close') : t('Menu')}</HamburgerMenuTextStyled>
        </HamburgerMenuStyled>
    );
};

/* @component */
export default HamburgerMenu;
