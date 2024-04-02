import imageLogo from '/public/images/logo.svg';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { Image } from 'components/Basic/Image/Image';
import { twMergeCustom } from 'utils/twMerge';

export const Logo: FC = () => (
    <>
        <LogoMetadata />
        <ExtendedNextLink className="flex-1 vl:flex-none" href="/" type="homepage">
            <Image
                priority
                alt="Shopsys logo"
                className={twMergeCustom('flex w-32 max-w-full lg:w-40')}
                src={imageLogo}
            />
        </ExtendedNextLink>
    </>
);
