import { SocialNetworkIcon } from './SocialNetworkLoginLinkIcon';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeLoginTypeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import { UrlObject } from 'url';

export const SocialNetworkLoginLink: FC<{ href: UrlObject; socialNetwork: TypeLoginTypeEnum }> = ({
    href,
    socialNetwork,
}) => {
    return (
        <ExtendedNextLink
            href={href}
            className={twJoin(
                'flex size-14 items-center justify-center rounded-lg',
                socialNetwork === TypeLoginTypeEnum.Facebook && 'bg-gradient-to-b from-[#19AFFF] to-[#0062E0]',
                socialNetwork === TypeLoginTypeEnum.Google && 'border-2 border-backgroundBrand',
                socialNetwork === TypeLoginTypeEnum.Seznam && 'bg-[#CC0000]',
            )}
        >
            <SocialNetworkIcon socialNetwork={socialNetwork} />
        </ExtendedNextLink>
    );
};
