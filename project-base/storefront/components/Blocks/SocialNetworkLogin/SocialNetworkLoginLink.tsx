import { UrlObject } from 'node:url';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeLoginTypeEnum } from 'graphql/types';
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

export const SocialNetworkLoginLink: FC<{ href: UrlObject; socialNetwork: TypeLoginTypeEnum }> = ({
    href,
    socialNetwork,
}) => {
    const { t } = useTranslation();
    const socialNetworkName = getSocialNetworkName(socialNetwork);
    const label = t('Continue with {{ socialNetwork }}', { socialNetwork: socialNetworkName });

    return (
        <ExtendedNextLink
            aria-label={label}
            href={href}
            title={label}
            className="flex w-full items-center justify-center gap-2 rounded-input border bg-background-default p-3 text-text-default no-underline transition-colors hover:bg-background-more hover:no-underline"
        >
            <SocialNetworkIcon socialNetwork={socialNetwork} />
            <span className="min-w-0 truncate">{label}</span>
        </ExtendedNextLink>
    );
};
