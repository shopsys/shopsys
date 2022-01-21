import { AnchorHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FlagStyled } from './Flag.style';

type NativeProps = ExtractNativePropsFromDefault<AnchorHTMLAttributes<HTMLAnchorElement>, 'href', never>;

type FlagProps = NativeProps & {
    color?: string;
    'data-testid'?: string;
};

const Flag: FC<FlagProps> = (props) => {
    const testIdentifier = props['data-testid'] ?? 'basic-flag';

    return (
        <FlagStyled href={props.href} color={props.color} data-testid={testIdentifier}>
            {props.children}
        </FlagStyled>
    );
};

export default Flag;
