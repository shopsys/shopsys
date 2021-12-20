import { SubMenuItemStyled, SubMenuStyled } from './SubMenu.style';
import Link from 'next/link';
import { ReactElement } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const SubMenu = (): ReactElement => {
    const testIdentifier = 'layout-header-dropdownmenu-submenu';
    const t = useTypedTranslationFunction();

    return (
        <SubMenuStyled data-testid={testIdentifier}>
            <Link href="/" passHref>
                <SubMenuItemStyled data-testid={testIdentifier + '-0'}>{t('Customer service')}</SubMenuItemStyled>
            </Link>
            <Link href="/" passHref>
                <SubMenuItemStyled data-testid={testIdentifier + '-1'}>{t('Stores')}</SubMenuItemStyled>
            </Link>
            <Link href="/" passHref>
                <SubMenuItemStyled data-testid={testIdentifier + '-2'}>{t('Sign in')}</SubMenuItemStyled>
            </Link>
        </SubMenuStyled>
    );
};

/* @component */
export default SubMenu;
