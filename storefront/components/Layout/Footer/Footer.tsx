import { FooterBlockStyled, FooterBottomStyled, FooterStyled } from './Footer.style';
import { FC } from 'react';
import FooterBoxInfo from './FooterBoxInfo';
import FooterContact from './FooterContact';
import FooterCopyright from './FooterCopyright';
import FooterMenu from './FooterMenu';
import { useRouter } from 'next/router';

const Footer: FC = () => {
    const router = useRouter();
    const isOrderPageLayoutVisible = router.route.slice(0, 6) === '/order';
    return (
        <FooterStyled>
            <FooterBottomStyled>
                {isOrderPageLayoutVisible === true ? (
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
