import { WeblinePropType } from './propTypes';
import { ContainerStyled, WeblineStyled } from './Webline.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, 'children', 'style'>;

type WeblineProps = NativeProps & WeblinePropType;

export const Webline: FC<WeblineProps> = (props) => {
    const testIdentifier =
        props['data-testid'] ?? 'layout-webline' + (props.type !== undefined ? '-' + props.type : '');

    return (
        <WeblineStyled {...props} data-testid={testIdentifier}>
            <ContainerStyled>{props.children}</ContainerStyled>
        </WeblineStyled>
    );
};
