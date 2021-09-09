import { ContainerStyled, WeblineStyled } from './Webline.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, 'children', 'style'>;

type WeblineProps = NativeProps & {
    /**
     * Type for change type of webline. If you don't fill this prop then the webline will be without style.
     */
    type?: 'colored' | 'dark' | 'light';
};

const Webline: FC<WeblineProps> = (props) => {
    return (
        <WeblineStyled {...props}>
            <ContainerStyled>{props.children}</ContainerStyled>
        </WeblineStyled>
    );
};

/* @component */
export default Webline;
