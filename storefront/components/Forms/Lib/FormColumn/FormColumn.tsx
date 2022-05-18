import { FormColumnStyled } from './FormColumn.style';
import { FormColumnPropType } from './propTypes';
import { FC } from 'react';

type FormColumnProps = FormColumnPropType;

const FormColumn: FC<FormColumnProps> = (props) => {
    return <FormColumnStyled {...props}>{props.children}</FormColumnStyled>;
};

/* @component */
export default FormColumn;
