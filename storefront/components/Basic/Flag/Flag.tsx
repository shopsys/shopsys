import { FlagStyled } from './Flag.style';
import NextLink from 'next/link';
import { AnchorHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<AnchorHTMLAttributes<HTMLAnchorElement>, 'href', never>;

type FlagProps = NativeProps & {
    color?: string;
    testIdentifier?: string;
};

const getTestIdentifier = (testIdentifier?: string) => testIdentifier ?? 'basic-flag';

export const Flag: FC<FlagProps> = ({ children, color, testIdentifier, href }) => (
    <NextLink href={href} passHref>
        <FlagStyled color={color} data-testid={getTestIdentifier(testIdentifier)}>
            {children}
        </FlagStyled>
    </NextLink>
);
