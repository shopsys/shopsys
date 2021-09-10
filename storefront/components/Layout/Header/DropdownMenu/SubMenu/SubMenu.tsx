import { SubMenuItemStyled, SubMenuStyled } from './SubMenu.style';
import Link from 'next/link';
import { ReactElement } from 'react';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const SubMenu = (): ReactElement => {
    const t = useTypedTranslationFunction();

    return (
        <SubMenuStyled>
            <Link href="/" passHref>
                <SubMenuItemStyled>{t('Customer service')}</SubMenuItemStyled>
            </Link>
            <Link href="/" passHref>
                <SubMenuItemStyled>{t('Stores')}</SubMenuItemStyled>
            </Link>
            <Link href="/" passHref>
                <SubMenuItemStyled>{t('Sign in')}</SubMenuItemStyled>
            </Link>
        </SubMenuStyled>
    );
};

/* @component */
export default SubMenu;
