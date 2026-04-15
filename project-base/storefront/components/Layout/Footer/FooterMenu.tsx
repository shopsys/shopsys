import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem';
import { FooterArticle } from 'types/footerArticle';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { FooterContacts } from './FooterContacts';
import { FooterContainer } from './FooterContainer';

type FooterMenuProps = {
    footerArticles: FooterArticle[];
};

export const FooterMenu: FC<FooterMenuProps> = ({ footerArticles }) => {
    const { t } = useTranslation();

    return (
        <FooterContainer className="bg-background-accent-less">
            <nav
                aria-label={t('Footer navigation', { ns: 'accessibility' })}
                className="flex vl:flex-row flex-col gap-7 lg:gap-6"
            >
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
