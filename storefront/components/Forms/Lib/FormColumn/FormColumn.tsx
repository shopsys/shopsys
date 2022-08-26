import { FormColumnStyled } from './FormColumn.style';
import { FormColumnPropType } from './propTypes';
import { FC } from 'react';

type FormColumnProps = FormColumnPropType;

export const FormColumn: FC<FormColumnProps> = ({ children, ...columnProps }) => (
    <FormColumnStyled {...columnProps}>{children}</FormColumnStyled>
);
