import { FooterContact } from './FooterContact';
import { FooterMenuItem } from 'components/Layout/Footer/FooterMenuItem';
import { FooterArticle } from 'types/footerArticle';

type FooterMenuProps = {
    footerArticles: FooterArticle[];
};

export const FooterMenu: FC<FooterMenuProps> = ({ footerArticles }) => {
    return (
        <div className="flex w-full flex-col flex-wrap gap-6 text-center lg:flex-row lg:justify-center lg:text-left vl:flex-nowrap vl:justify-between">
            {footerArticles.map((item) => (
                <div key={item.key} className="flex-1">
                    <FooterMenuItem items={item.items} title={item.title} />
                </div>
            ))}

            <div className="flex basis-full flex-col items-center vl:flex-1">
                <FooterContact />
            </div>
        </div>
    );
};
