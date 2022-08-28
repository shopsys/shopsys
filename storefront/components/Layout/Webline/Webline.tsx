import { WeblinePropType, WeblineType } from './propTypes';
import { ContainerStyled, WeblineStyled } from './Webline.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style' | 'className'>;

type WeblineProps = NativeProps & WeblinePropType;

const getTestIdentifier = (testIdentifier?: string, type?: WeblineType) =>
    testIdentifier ?? 'layout-webline' + (type !== undefined ? '-' + type : '');

export const Webline: FC<WeblineProps> = ({ children, style, testIdentifier, type, className }) => (
    <WeblineStyled
        className={className}
        style={style}
        type={type}
        testIdentifier={getTestIdentifier(testIdentifier, type)}
    >
        <ContainerStyled>{children}</ContainerStyled>
    </WeblineStyled>
);
