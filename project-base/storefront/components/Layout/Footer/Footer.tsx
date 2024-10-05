import { FooterCopyright } from './FooterCopyright';
import { FooterMenu } from './FooterMenu';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getCouldNotFindUserConsentPolicyArticleUrl } from 'components/Blocks/UserConsent/userConsentUtils';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { FooterArticle } from 'types/footerArticle';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export type FooterProps =
    | {
          simpleFooter: true;
          footerArticles?: never;
      }
    | {
          simpleFooter?: never;
          footerArticles?: FooterArticle[];
      };

export const Footer: FC<FooterProps> = ({ simpleFooter, footerArticles }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [settingsResponse] = useSettingsQuery();
    const [userConsentUrl] = getInternationalizedStaticUrls(['/user-consent'], url);

    return (
        <div className="relative mt-auto">
            <div className="flex flex-col pb-11 pt-5 lg:py-11">
                {!simpleFooter && (
                    <>
                        {!!footerArticles?.length && (
                            <div className="mb-12 vl:mb-24 vl:flex">
                                <FooterMenu footerArticles={footerArticles} />
                            </div>
                        )}
                    </>
                )}
                <FooterCopyright />
                {!getCouldNotFindUserConsentPolicyArticleUrl(settingsResponse) && (
                    <ExtendedNextLink className="self-center transition" href={userConsentUrl}>
                        {t('User consent update')}
                    </ExtendedNextLink>
                )}
            </div>
        </div>
    );
};
