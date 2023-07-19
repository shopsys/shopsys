import { LogoMetadata } from 'components/Basic/Head/LogoMetadata';
import { Link } from 'components/Basic/Link/Link';
import NextImage from 'next/image';

const TEST_IDENTIFIER = 'layout-header-logo';

export const Logo: FC = () => (
    <>
        <LogoMetadata />
        <Link href="/">
            <NextImage
                src="/images/logo.svg"
                width={163}
                height={38}
                alt="Shopsys logo"
                data-testid={TEST_IDENTIFIER}
                className="flex w-32 max-w-full lg:w-40"
                alt="logo"
            />
        </Link>
    </>
);
