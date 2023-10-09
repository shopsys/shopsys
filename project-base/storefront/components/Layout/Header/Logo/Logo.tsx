import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { twMergeCustom } from 'helpers/twMerge';
import NextImage from 'next/image';

const TEST_IDENTIFIER = 'layout-header-logo';

export const Logo: FC = () => (
    <>
        <LogoMetadata />
        <ExtendedNextLink className="flex-1 vl:flex-none" href="/" type="homepage">
            <NextImage
                priority
                alt="Shopsys logo"
                className={twMergeCustom('flex w-32 max-w-full lg:w-40')}
                data-testid={TEST_IDENTIFIER}
                height={38}
                src="/images/logo.svg"
                width={163}
            />
        </ExtendedNextLink>
    </>
);
