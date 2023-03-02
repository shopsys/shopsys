import { FormColumnStyled } from './FormColumn.style';
import { FormColumnPropType } from './propTypes';

type FormColumnProps = FormColumnPropType;

export const FormColumn: FC<FormColumnProps> = ({ children, ...columnProps }) => (
    <FormColumnStyled {...columnProps}>{children}</FormColumnStyled>
);
