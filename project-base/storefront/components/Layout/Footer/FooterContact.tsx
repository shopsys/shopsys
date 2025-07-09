import { FacebookIcon } from 'components/Basic/Icon/FacebookIcon';
import { InstagramIcon } from 'components/Basic/Icon/InstagramIcon';
import { YoutubeIcon } from 'components/Basic/Icon/YoutubeIcon';
import { IconImage } from 'components/Basic/IconImage/IconImage';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';

export const FooterContact: FC = () => {
    const { t } = useTranslation();

    return (
        <>
            <div className="h4 mb-3 text-center uppercase">{t('Follow Us')}</div>

            <div
                className="border-border-default flex h-24 w-full max-w-xs overflow-hidden rounded-sm border-2"
                data-tid={TIDs.footer_social_links}
            >
                <FooterContactSocialsItem ariaLabel={t('Go to Instagram')} href="#" title="Instagram">
                    <InstagramIcon className="text-text-default w-8" />
                </FooterContactSocialsItem>
                <FooterContactSocialsItem ariaLabel={t('Go to Facebook')} href="#" title="Facebook">
                    <FacebookIcon className="w-11 text-[#1877f2]" />
                </FooterContactSocialsItem>
                <FooterContactSocialsItem ariaLabel={t('Go to Youtube')} href="#" title="Youtube">
                    <YoutubeIcon className="w-11 text-[#d93738]" />
                </FooterContactSocialsItem>
            </div>

            <div className="mt-4 flex flex-wrap justify-center gap-5">
                <FooterContactLangsItem ariaLabel={t('Change language to Czechia')} href="#" text={t('Czechia')}>
                    <IconImage alt={t('Czechia flag')} height={16} icon="cz" tabIndex={-1} width={24} />
                </FooterContactLangsItem>
                <FooterContactLangsItem ariaLabel={t('Change language to Slovakia')} href="#" text={t('Slovakia')}>
                    <IconImage alt={t('Slovakia flag')} height={16} icon="sk" tabIndex={-1} width={24} />
                </FooterContactLangsItem>
            </div>
        </>
    );
};

const FooterContactSocialsItem: FC<{ href: string; title: string; ariaLabel: string }> = ({
    children,
    title,
    href,
    ariaLabel,
}) => (
    <a
        aria-label={ariaLabel}
        className="flex h-full w-1/3 items-center justify-center first:border-none"
        href={href}
        tabIndex={0}
        title={title}
    >
        {children}
    </a>
);

const FooterContactLangsItem: FC<{ href: string; text: string; ariaLabel: string }> = ({
    children,
    href,
    text,
    ariaLabel,
}) => (
    <a
        aria-label={ariaLabel}
        className="text-link-default hover:text-link-hovered flex items-center rounded-sm hover:no-underline"
        href={href}
        tabIndex={0}
        title={text}
    >
        {children}
        <span className="ml-2 text-sm">{text}</span>
    </a>
);
