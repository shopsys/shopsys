import { LogoStyled } from './Logo.style';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata/LogoMetadata';
import Link from 'components/Basic/Link';
import { ReactElement } from 'react';

const Logo = (): ReactElement => {
    const testIdentifier = 'layout-header-logo';

    return (
        <>
            <LogoMetadata />
            <Link href="/">
                <LogoStyled src="/images/logo.svg" width={163} height={38} data-testid={testIdentifier} />
            </Link>
        </>
    );
};

/* @component */
export default Logo;
