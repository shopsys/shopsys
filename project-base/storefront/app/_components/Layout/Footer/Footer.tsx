import { FooterCopyright } from './FooterCopyright';
import { getFooterArticlesQuery } from 'app/_queries/getFooterArticlesQuery';
import { createQuery } from 'app/_urql/urql-dto';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getCouldNotFindUserConsentPolicyArticleUrl } from 'components/Blocks/UserConsent/userConsentUtils';
import { FooterMenu } from 'components/Layout/Footer/FooterMenu';
import {
    SettingsQueryDocument,
    TypeSettingsQuery,
    TypeSettingsQueryVariables,
} from 'graphql/requests/settings/queries/SettingsQuery.ssr';
import { headers } from 'next/headers';
import { FooterArticle } from 'types/footerArticle';
import { getDomainConfig } from 'utils/domain/domainConfig';
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

export const Footer: FC<FooterProps> = async ({ simpleFooter }) => {
    const t = await getTranslation();
    const { url } = getDomainConfig(headers().get('host')!);

    const settingsResponse = await createQuery<TypeSettingsQuery, TypeSettingsQueryVariables>(
        SettingsQueryDocument,
        {},
    );

    const footerArticles = await getFooterArticlesQuery();
    const [userConsentUrl] = getInternationalizedStaticUrls(['/user-consent'], url);

    return (
        <div className="relative mt-auto">
            <div className="flex flex-col pt-5 pb-11 lg:py-11">
                {!simpleFooter && (
                    <>
                        {!!footerArticles.length && (
                            <div className="vl:mb-24 vl:flex mb-12">
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
