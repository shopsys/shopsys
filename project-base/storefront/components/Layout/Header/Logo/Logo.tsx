import imageLogoInverted from '/public/images/logo-inverted.svg';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

export const Logo: FC = () => {
    const { t } = useTranslation();
    return (
        <>
            <LogoMetadata />

            <ExtendedNextLink
                aria-label={t('Go to homepage')}
                className="vl:flex-none group order-2 flex-1 rounded-md px-2 sm:px-3 lg:order-1 lg:px-0"
                href="/"
                tid={TIDs.header_homepage_link}
                title={t('Homepage')}
                type="homepage"
            >
                <Image
                    priority
                    alt="Shopsys logo"
                    className={twMergeCustom('flex w-32 max-w-full group-focus-visible:brightness-0 lg:w-40')}
                    height={38}
                    sizes="(max-width: 1023px) 128px, 160px"
                    src={imageLogoInverted}
                    width={160}
                />
            </ExtendedNextLink>
        </>
    );
};
