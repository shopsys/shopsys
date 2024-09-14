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
            className="flex-1 vl:flex-none order-2 lg:order-1 px-2 sm:px-3 lg:px-0"
            href="/"
            tid={TIDs.header_homepage_link}
            type="homepage"
        >
            <Image
                priority
                alt="Shopsys logo"
                className={twMergeCustom('flex w-32 max-w-full lg:w-40')}
                src={imageLogoInverted}
            />
        </ExtendedNextLink>
    </>
);
