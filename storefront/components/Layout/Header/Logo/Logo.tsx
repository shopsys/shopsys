import { LogoStyled } from './Logo.style';
import { LogoMetadata } from 'components/Basic/Head/LogoMetadata/LogoMetadata';
import { Link } from 'components/Basic/Link/Link';
import { FC } from 'react';

export const Logo: FC = () => {
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
