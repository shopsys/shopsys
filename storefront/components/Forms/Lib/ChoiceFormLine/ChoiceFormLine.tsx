import { ChoiceFormLineStyled } from './ChoiceFormLine.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type ChoiceFormLineProps = NativeProps;

export const ChoiceFormLine: FC<ChoiceFormLineProps> = ({ children, style }) => (
    <ChoiceFormLineStyled style={style}>{children}</ChoiceFormLineStyled>
);
