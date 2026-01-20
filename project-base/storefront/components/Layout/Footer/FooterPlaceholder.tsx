import { FooterProps } from './Footer';
import { FooterContainer } from './FooterContainer';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { removeSpaces } from 'utils/removeSpaces';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useContacts } from 'utils/useContacts';

export const FooterPlaceholder: FC<FooterProps> = ({ simpleFooter, footerArticles }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [userConsentUrl] = getInternationalizedStaticUrls(['/user-consent'], url);
    const currentYear = new Date().getFullYear();
    const { phone, openingHours, email, emailSubtitle } = useContacts();
    const cleanPhone = removeSpaces(phone);

    return (
        <>
            {!simpleFooter && !!footerArticles?.length && (
                <FooterContainer className="bg-background-accent-less">
                    <nav className="vl:flex-row flex flex-col gap-7 lg:gap-6">
                        <div className="flex flex-1 flex-col gap-1.5 lg:flex-row lg:gap-6">
                            {footerArticles.map((item) => (
                                <div key={item.title} className="hidden lg:flex lg:flex-1 lg:flex-col lg:gap-6">
                                    <h3 className="font-secondary text-xs font-semibold tracking-wider uppercase">
                                        {item.title}
                                    </h3>

                                    <ul className="space-y-2 lg:space-y-4">
                                        {item.items.map((article) => (
                                            <li key={article.uuid}>
                                                <ExtendedNextLink
                                                    className="text-text-default hover:text-text-default font-secondary block text-sm font-semibold tracking-wider no-underline hover:underline"
                                                    rel={article.external ? 'nofollow noreferrer noopener' : undefined}
                                                    target={article.external ? '_blank' : undefined}
                                                    href={
                                                        article.__typename === 'ArticleSite'
                                                            ? article.slug
                                                            : article.url
                                                    }
                                                >
                                                    {article.name}
                                                </ExtendedNextLink>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>

                        {/* Contacts skeleton with data for crawlers */}
                        <div className="flex flex-col gap-4">
                            <div className="bg-background-default relative flex gap-4 rounded-lg p-4">
                                <a
                                    aria-label={t('Call us', { ns: 'accessibility' })}
                                    className="sr-only"
                                    href={'tel:' + cleanPhone}
                                >
                                    {phone} - {openingHours}
                                </a>
                                <Skeleton className="size-6" />
                                <div className="flex flex-col gap-1">
                                    <Skeleton className="h-5 w-32" />
                                    <Skeleton className="h-3 w-24" />
                                </div>
                            </div>

                            <div className="bg-background-default relative flex gap-4 rounded-lg p-4">
                                <a
                                    aria-label={t('Write to us', { ns: 'accessibility' })}
                                    className="sr-only"
                                    href={'mailto:' + email}
                                >
                                    {email} - {emailSubtitle}
                                </a>
                                <Skeleton className="size-6" />
                                <div className="flex flex-col gap-1">
                                    <Skeleton className="h-5 w-36" />
                                    <Skeleton className="h-3 w-28" />
                                </div>
                            </div>
                        </div>
                    </nav>
                </FooterContainer>
            )}

            {!simpleFooter && (
                <FooterContainer>
                    <div className="flex flex-col items-center justify-between gap-5 lg:flex-row">
                        <Skeleton className="h-16 w-64 rounded-lg" />

                        <div data-tid={TIDs.footer_social_links}>
                            <span className="sr-only">
                                <a href="/" rel="noreferrer" target="_blank">
                                    Instagram
                                </a>
                                <a href="/" rel="noreferrer" target="_blank">
                                    Facebook
                                </a>
                                <a href="/" rel="noreferrer" target="_blank">
                                    Youtube
                                </a>
                            </span>
                            <Skeleton className="h-10 w-32 rounded-lg" />
                        </div>
                    </div>
                </FooterContainer>
            )}

            <FooterContainer>
                <div className="flex flex-col items-center gap-5 lg:gap-2">
                    <div
                        className="text-text-default flex w-full items-center justify-center text-center text-sm"
                        data-tid={TIDs.footer_copyright}
                    >
                        {t('footerCopyright', { currentYear })}
                    </div>

                    <div className="text-text-default flex items-center justify-center gap-1.5 text-sm">
                        <a className="sr-only" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                            {t('Customized E-shop by')} Shopsys
                        </a>
                        <Skeleton className="h-6 w-48" />
                    </div>

                    <ExtendedNextLink className="text-sm leading-6" href={userConsentUrl}>
                        {t('User consent update')}
                    </ExtendedNextLink>
                </div>
            </FooterContainer>
        </>
    );
};
