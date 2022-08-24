import { FormColumnStyled } from './FormColumn.style';
import { FormColumnPropType } from './propTypes';
import { FC } from 'react';

type FormColumnProps = FormColumnPropType;

export const FormColumn: FC<FormColumnProps> = (props) => {
    return <FormColumnStyled {...props}>{props.children}</FormColumnStyled>;
};
