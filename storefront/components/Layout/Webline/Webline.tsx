import { ContainerStyled, WeblineStyled } from './Webline.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { WeblinePropType } from './propTypes';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, 'children', 'style'>;

type WeblineProps = NativeProps & WeblinePropType;

const Webline: FC<WeblineProps> = (props) => {
    return (
        <WeblineStyled {...props}>
            <ContainerStyled>{props.children}</ContainerStyled>
        </WeblineStyled>
    );
};

/* @component */
export default Webline;
