import { FC } from 'react';
import { FormColumnProps } from './types';
import { FormColumnStyled } from './FormColumn.style';

const FormColumn: FC<FormColumnProps> = (props) => {
    return <FormColumnStyled {...props}>{props.children}</FormColumnStyled>;
};

/* @component */
export default FormColumn;
