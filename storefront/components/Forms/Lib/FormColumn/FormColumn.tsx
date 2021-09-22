import { FC } from 'react';
import { FormColumnStyled } from './FormColumn.style';

const FormColumn: FC = (props) => {
    return <FormColumnStyled>{props.children}</FormColumnStyled>;
};

/* @component */
export default FormColumn;
