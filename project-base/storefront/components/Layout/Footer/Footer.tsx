import { FooterBoxInfo } from './FooterBoxInfo';
import { FooterCopyright } from './FooterCopyright';
import { FooterMenu } from './FooterMenu';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { FooterArticle } from 'types/footerArticle';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type FooterProps =
    | {
          simpleFooter: true;
          footerArticles?: never;
          phone?: never;
          opening?: never;
      }
    | {
          simpleFooter?: never;
          footerArticles?: FooterArticle[];
          phone: string;
          opening: string;
      };

export const Footer: FC<FooterProps> = ({ simpleFooter, footerArticles, phone, opening }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);

    return (
        <div className="relative mt-auto">
            <div className="flex flex-col pt-5 pb-11 lg:py-11">
                {!simpleFooter && (
                    <>
                        <FooterBoxInfo opening={opening} phone={phone} />
                        {!!footerArticles?.length && (
                            <div className="mb-12 vl:mb-24 vl:flex">
                                <FooterMenu footerArticles={footerArticles} />
                            </div>
                        )}
                    </>
                )}
                <FooterCopyright />
                <ExtendedNextLink
                    className="self-center text-greyLight no-underline transition hover:text-whitesmoke hover:no-underline"
                    href={cookieConsentUrl}
                >
                    {t('Cookie consent update')}
                </ExtendedNextLink>
            </div>
        </div>
    );
};
