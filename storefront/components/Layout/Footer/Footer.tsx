import { FooterBlockStyled, FooterBottomStyled, FooterStyled } from './Footer.style';
import FooterBoxInfo from './FooterBoxInfo';
import FooterContact from './FooterContact';
import FooterCopyright from './FooterCopyright';
import FooterMenu from './FooterMenu';
import { FC } from 'react';

type FooterProps = {
    simpleFooter?: boolean;
};

const Footer: FC<FooterProps> = ({ simpleFooter }) => {
    const testIdentifier = 'layout-footer';

    return (
        <FooterStyled data-testid={testIdentifier}>
            <FooterBottomStyled>
                {simpleFooter ? (
                    <FooterCopyright />
                ) : (
                    <>
                        <FooterBoxInfo />
                        <FooterBlockStyled>
                            <FooterMenu />
                            <FooterContact />
                        </FooterBlockStyled>
                        <FooterCopyright />
                    </>
                )}
            </FooterBottomStyled>
        </FooterStyled>
    );
};

export default Footer;
