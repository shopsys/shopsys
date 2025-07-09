import { FooterContact } from './FooterContact';
import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem';
import useTranslation from 'next-translate/useTranslation';
import { FooterArticle } from 'types/footerArticle';

type FooterMenuProps = {
    footerArticles: FooterArticle[];
};

export const FooterMenu: FC<FooterMenuProps> = ({ footerArticles }) => {
    const { t } = useTranslation();

    return (
        <nav aria-label={t('Footer navigation')}>
            <div className="vl:flex-nowrap vl:justify-between flex w-full flex-col flex-wrap gap-6 text-center lg:flex-row lg:justify-center lg:text-left">
                {footerArticles.map((item) => (
                    <div key={item.key} className="flex-1">
                        <FooterMenuItem items={item.items} title={item.title} />
                    </div>
                ))}

                <div className="vl:flex-1 flex basis-full flex-col items-center">
                    <FooterContact />
                </div>
            </div>
        </nav>
    );
};
