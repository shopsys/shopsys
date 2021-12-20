import Link from 'components/Basic/Link';
import { LogoStyled } from './Logo.style';
import { ReactElement } from 'react';

const Logo = (): ReactElement => {
    const testIdentifier = 'layout-header-logo';
    return (
        <Link href="/">
            <LogoStyled src="/images/logo.svg" width={163} height={38} data-testid={testIdentifier} />
        </Link>
    );
};

/* @component */
export default Logo;
