import { UrlObject } from 'node:url';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LastUsedLoginMethodBadge } from 'components/Blocks/Login/LastUsedLoginMethodBadge';
import { TIDs } from 'cypress/tids';
import { TypeLoginTypeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { SocialNetworkIcon } from './SocialNetworkLoginLinkIcon';

const getSocialNetworkName = (socialNetwork: TypeLoginTypeEnum) => {
    switch (socialNetwork) {
        case TypeLoginTypeEnum.Facebook:
            return 'Facebook';
        case TypeLoginTypeEnum.Google:
            return 'Google';
        case TypeLoginTypeEnum.Seznam:
            return 'Seznam';
        default:
            return socialNetwork;
    }
};

type SocialNetworkLoginLinkProps = {
    href: UrlObject;
    isLastUsed: boolean;
    socialNetwork: TypeLoginTypeEnum;
};

export const SocialNetworkLoginLink: FC<SocialNetworkLoginLinkProps> = ({ href, isLastUsed, socialNetwork }) => {
    const { t } = useTranslation();
    const socialNetworkName = getSocialNetworkName(socialNetwork);
    const label = t('Continue with {{ socialNetwork }}', { socialNetwork: socialNetworkName });
    const lastUsedLabel = t('Last used');
    const accessibleLabel = isLastUsed ? `${label}. ${lastUsedLabel}` : label;

    return (
        <ExtendedNextLink
            aria-label={accessibleLabel}
            href={href}
            tid={`${TIDs.social_network_login_link_}${socialNetwork}`}
            className={twJoin(
                'relative flex w-full items-center justify-center gap-2 rounded-input bg-background-default text-text-default no-underline transition-colors hover:bg-background-more hover:no-underline',
                isLastUsed ? 'border-2 border-background-accent p-2.75' : 'border p-3',
            )}
        >
            <SocialNetworkIcon socialNetwork={socialNetwork} />
            <span className="min-w-0 truncate">{label}</span>
            {isLastUsed && <LastUsedLoginMethodBadge />}
        </ExtendedNextLink>
    );
};
