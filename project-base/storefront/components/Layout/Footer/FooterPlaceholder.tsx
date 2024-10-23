import { FooterProps } from './Footer';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import imageLogo from 'public/images/logo.svg';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const FooterPlaceholder: FC<FooterProps> = ({ simpleFooter, footerArticles }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [userConsentUrl, contactUrl] = getInternationalizedStaticUrls(['/user-consent', '/contact'], url);
    const currentYear = new Date().getFullYear();

    return (
        <>
            {!simpleFooter && (
                <>
                    <ExtendedNextLink href={contactUrl}>{t('Write to us')}</ExtendedNextLink>
                    {footerArticles?.map((item) => (
                        <div key={item.title}>
                            <h3>{item.title}</h3>
                            <ul>
                                {item.items.map((item) => (
                                    <li key={item.uuid}>
                                        <ExtendedNextLink
                                            href={item.__typename === 'ArticleSite' ? item.slug : item.url}
                                            rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                                            target={item.external ? '_blank' : undefined}
                                        >
                                            {item.name}
                                        </ExtendedNextLink>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </>
            )}
            {t('Copyright © {{ currentYear }}, Shopsys s.r.o. All rights reserved.', { currentYear })}
            {t('Customized E-shop by')}
            <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                <Image alt="footer logo" src={imageLogo} />
            </a>
            <ExtendedNextLink
                className="text-greyLight hover:text-whitesmoke self-center no-underline transition hover:no-underline"
                href={userConsentUrl}
            >
                {t('User consent update')}
            </ExtendedNextLink>
        </>
    );
};
