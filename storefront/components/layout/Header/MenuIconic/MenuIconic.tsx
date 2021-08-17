import {
    MenuIconicButtonMobileLinkStyled,
    MenuIconicButtonMobileStyled,
    MenuIconicItemLinkStyled,
    MenuIconicItemStyled,
    MenuIconicListStyled,
} from './MenuIconic.style';
import Link from 'next/link';
import { ReactElement } from 'react';
import { useTranslation } from 'react-i18next';

const MenuIconic = (): ReactElement => {
    const { t } = useTranslation();

    return (
        <>
            <MenuIconicListStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <img src="/svg/chat.svg" alt="" width={18} />
                            {t<string>('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <img src="/svg/marker.svg" alt="" width={18} />
                            {t<string>('Stores')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <img src="/svg/user.svg" alt="" width={18} />
                            {t<string>('Sign in')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
            </MenuIconicListStyled>
            <MenuIconicButtonMobileStyled>
                <Link href="/" passHref>
                    <MenuIconicButtonMobileLinkStyled>
                        <img src="/svg/user.svg" alt="" width={18} />
                    </MenuIconicButtonMobileLinkStyled>
                </Link>
            </MenuIconicButtonMobileStyled>
        </>
    );
};

/* @component */
export default MenuIconic;
