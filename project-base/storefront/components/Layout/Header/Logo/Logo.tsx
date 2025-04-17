import imageLogoInverted from '/public/images/logo-inverted.svg';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { twMergeCustom } from 'utils/twMerge';

export const Logo: FC = () => (
    <>
        <LogoMetadata />

        <ExtendedNextLink
            className="vl:flex-none focus-visible:ring-background order-2 flex-1 px-2 focus-visible:ring-2 sm:px-3 lg:order-1 lg:px-0"
            href="/"
            tid={TIDs.header_homepage_link}
            type="homepage"
        >
            <Image
                priority
                alt="Shopsys logo"
                className={twMergeCustom('flex w-32 max-w-full p-1 lg:w-40')}
                src={imageLogoInverted}
            />
        </ExtendedNextLink>
    </>
);
