import { LogoStyled } from './Logo.style';
import { ReactElement } from 'react';
import ShopsysLink from '../../../basic/ShopsysLink/ShopsysLink';

const Logo = (): ReactElement => {
    return (
        <ShopsysLink href="/">
            <LogoStyled src="/images/logo.svg" width={163} height={38} />
        </ShopsysLink>
    );
};

/* @component */
export default Logo;
