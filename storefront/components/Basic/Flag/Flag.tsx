import { FlagStyled } from './Flag.style';
import NextLink from 'next/link';
import { AnchorHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<AnchorHTMLAttributes<HTMLAnchorElement>, 'href', never>;

type FlagProps = NativeProps & {
    color?: string;
    'data-testid'?: string;
};

const Flag: FC<FlagProps> = (props) => {
    const testIdentifier = props['data-testid'] ?? 'basic-flag';

    return (
        <NextLink href={props.href} passHref>
            <FlagStyled color={props.color} data-testid={testIdentifier}>
                {props.children}
            </FlagStyled>
        </NextLink>
    );
};

export default Flag;
