import {
    MenuIconicButtonMobileLinkStyled,
    MenuIconicButtonMobileStyled,
    MenuIconicItemLinkStyled,
    MenuIconicItemStyled,
    MenuIconicListStyled,
} from './MenuIconic.style';
import Icon from '../../../Basic/Icon';
import NextLink from 'next/link';
import { ReactElement } from 'react';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const MenuIconic = (): ReactElement => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <MenuIconicListStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <Icon icon="NotImplementedYet" iconHeight={18} />
                            {t<string>('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <Icon icon="NotImplementedYet" iconHeight={18} />
                            {t<string>('Stores')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <Icon icon="NotImplementedYet" iconHeight={18} />
                            {t<string>('Sign in')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
            </MenuIconicListStyled>
            <MenuIconicButtonMobileStyled>
                <NextLink href="/" passHref>
                    <MenuIconicButtonMobileLinkStyled>
                        <Icon icon="NotImplementedYet" iconHeight={18} />
                    </MenuIconicButtonMobileLinkStyled>
                </NextLink>
            </MenuIconicButtonMobileStyled>
        </>
    );
};

/* @component */
export default MenuIconic;
