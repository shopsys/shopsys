import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { twMergeCustom } from 'helpers/twMerge';
import NextImage from 'next/image';

const TEST_IDENTIFIER = 'layout-header-logo';

export const Logo: FC = () => (
    <>
        <LogoMetadata />
        <ExtendedNextLink href="/" type="static" className="flex-1 vl:flex-none">
            <NextImage
                src="/images/logo.svg"
                width={163}
                height={38}
                alt="Shopsys logo"
                data-testid={TEST_IDENTIFIER}
                className={twMergeCustom('flex w-32 max-w-full lg:w-40')}
                priority
            />
        </ExtendedNextLink>
    </>
);
