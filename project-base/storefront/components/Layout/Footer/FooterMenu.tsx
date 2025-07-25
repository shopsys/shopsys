import { FooterContacts } from './FooterContacts';
import { FooterContainer } from './FooterContainer';
import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem';
import useTranslation from 'next-translate/useTranslation';
import { FooterArticle } from 'types/footerArticle';

type FooterMenuProps = {
    footerArticles: FooterArticle[];
};

export const FooterMenu: FC<FooterMenuProps> = ({ footerArticles }) => {
    const { t } = useTranslation();

    return (
        <FooterContainer className="bg-background-accent-less">
            <nav aria-label={t('Footer navigation')} className="vl:flex-row flex flex-col gap-7 lg:gap-6">
                <div className="flex flex-1 flex-col gap-1.5 lg:flex-row lg:gap-6">
                    {footerArticles.map((item) => (
                        <FooterMenuItem key={item.key} items={item.items} title={item.title} />
                    ))}
                </div>

                <FooterContacts />
            </nav>
        </FooterContainer>
    );
};
