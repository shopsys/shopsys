import {
    MenuIconicButtonMobileLinkStyled,
    MenuIconicButtonMobileStyled,
    MenuIconicItemLinkStyled,
    MenuIconicItemStyled,
    MenuIconicListStyled,
} from './MenuIconic.style';
import Icon from '../../../Basic/Icon';
import Link from 'next/link';
import { ReactElement } from 'react';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const MenuIconic = (): ReactElement => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <MenuIconicListStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <Icon icon="NotImplementedYet" iconHeight={18} />
                            {t<string>('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <Icon icon="NotImplementedYet" iconHeight={18} />
                            {t<string>('Stores')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <Link href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <Icon icon="NotImplementedYet" iconHeight={18} />
                            {t<string>('Sign in')}
                        </MenuIconicItemLinkStyled>
                    </Link>
                </MenuIconicItemStyled>
            </MenuIconicListStyled>
            <MenuIconicButtonMobileStyled>
                <Link href="/" passHref>
                    <MenuIconicButtonMobileLinkStyled>
                        <Icon icon="NotImplementedYet" iconHeight={18} />
                    </MenuIconicButtonMobileLinkStyled>
                </Link>
            </MenuIconicButtonMobileStyled>
        </>
    );
};

/* @component */
export default MenuIconic;
