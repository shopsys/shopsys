import { AnchorHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FlagStyled } from './Flag.style';

type NativeProps = ExtractNativePropsFromDefault<AnchorHTMLAttributes<HTMLAnchorElement>, 'href', never>;

type FlagProps = {
    color?: string;
};

const Flag: FC<FlagProps & NativeProps> = (props) => {
    return (
        <FlagStyled href={props.href} color={props.color}>
            {props.children}
        </FlagStyled>
    );
};

export default Flag;
