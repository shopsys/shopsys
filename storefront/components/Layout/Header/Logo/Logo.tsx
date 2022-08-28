import { LogoStyled } from './Logo.style';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata/LogoMetadata';
import { Link } from 'components/Basic/Link/Link';
import { FC } from 'react';

const TEST_IDENTIFIER = 'layout-header-logo';

export const Logo: FC = () => (
    <>
        <LogoMetadata />
        <Link href="/">
            <LogoStyled src="/images/logo.svg" width={163} height={38} data-testid={TEST_IDENTIFIER} />
        </Link>
    </>
);
