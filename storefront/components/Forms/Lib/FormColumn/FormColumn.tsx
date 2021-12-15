import { FC } from 'react';
import { FormColumnPropType } from './propTypes';
import { FormColumnStyled } from './FormColumn.style';

type FormColumnProps = FormColumnPropType;

const FormColumn: FC<FormColumnProps> = (props) => {
    return <FormColumnStyled {...props}>{props.children}</FormColumnStyled>;
};

/* @component */
export default FormColumn;
