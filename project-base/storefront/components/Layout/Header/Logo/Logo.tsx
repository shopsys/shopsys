import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import logo from '/public/images/logo.svg';

type LogoProps = {
    imageClassName?: string;
};

export const Logo: FC<LogoProps> = ({ imageClassName }) => {
    const { t } = useTranslation();

    return (
        <>
            <LogoMetadata />

            <ExtendedNextLink
                aria-label={t('Go to homepage', { ns: 'accessibility' })}
                className="group order-2 vl:order-1 flex-1 vl:flex-none rounded-md vl:px-0"
                href="/"
                tid={TIDs.header_homepage_link}
                title={t('Homepage')}
                type="homepage"
            >
                <Image
                    alt="Shopsys.com"
                    className={twMergeCustom(
                        'h-auto vl:w-40 w-32 max-w-full group-focus-visible:brightness-0',
                        imageClassName,
                    )}
                    src={logo}
                />
            </ExtendedNextLink>
        </>
    );
};
