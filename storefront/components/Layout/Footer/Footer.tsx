import { FooterBlockStyled, FooterBottomStyled, FooterStyled } from './Footer.style';
import { FC } from 'react';
import FooterBoxInfo from './FooterBoxInfo';
import FooterContact from './FooterContact';
import FooterCopyright from './FooterCopyright';
import FooterMenu from './FooterMenu';

const Footer: FC = () => {
    return (
        <FooterStyled>
            <FooterBottomStyled>
                <FooterBoxInfo />
                <FooterBlockStyled>
                    <FooterMenu />
                    <FooterContact />
                </FooterBlockStyled>
                <FooterCopyright />
            </FooterBottomStyled>
        </FooterStyled>
    );
};

export default Footer;
