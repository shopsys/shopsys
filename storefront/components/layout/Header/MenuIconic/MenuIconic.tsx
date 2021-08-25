import {
    MenuIconicButtonMobileLinkStyled,
    MenuIconicButtonMobileStyled,
    MenuIconicItemLinkStyled,
    MenuIconicItemStyled,
    MenuIconicListStyled,
} from './MenuIconic.style';
import Link from 'next/link';
import { ReactElement } from 'react';
import ShopsysIcon from '../../../basic/ShopsysIcon';
import { useTranslation } from 'react-i18next';

const MenuIconic = (): ReactElement => {
    const { t } = useTranslation();

    return (
        <>
            <MenuIconicListStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <ShopsysIcon icon="chat" iconHeight={18} />
                            {t<string>('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <ShopsysIcon icon="marker" iconHeight={18} />
                            {t<string>('Stores')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <ShopsysIcon icon="user" iconHeight={18} />
                            {t<string>('Sign in')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
            </MenuIconicListStyled>
            <MenuIconicButtonMobileStyled>
                <Link href="/" passHref>
                    <MenuIconicButtonMobileLinkStyled>
                        <ShopsysIcon icon="user" iconHeight={18} />
                    </MenuIconicButtonMobileLinkStyled>
                </Link>
            </MenuIconicButtonMobileStyled>
        </>
    );
};

/* @component */
export default MenuIconic;
