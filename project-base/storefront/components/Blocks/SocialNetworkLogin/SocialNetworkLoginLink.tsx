import { SocialNetworkIcon } from './SocialNetworkLoginLinkIcon';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeLoginTypeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import { UrlObject } from 'url';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const SocialNetworkLoginLink: FC<{ href: UrlObject; socialNetwork: TypeLoginTypeEnum }> = ({
    href,
    socialNetwork,
}) => {
    const { t } = useTranslation();
    return (
        <ExtendedNextLink
            aria-label={t('Use {{ socialNetwork }} to login', { ns: 'accessibility', socialNetwork })}
            href={href}
            title={t('Login with {{ socialNetwork }}', { socialNetwork })}
            className={twJoin(
                'flex size-14 items-center justify-center rounded-lg',
                socialNetwork === TypeLoginTypeEnum.Facebook && 'bg-linear-to-b/srgb from-[#19AFFF] to-[#0062E0]',
                socialNetwork === TypeLoginTypeEnum.Google && 'border-background-brand border-2',
                socialNetwork === TypeLoginTypeEnum.Seznam && 'bg-[#CC0000]',
            )}
        >
            <SocialNetworkIcon socialNetwork={socialNetwork} />
        </ExtendedNextLink>
    );
};
