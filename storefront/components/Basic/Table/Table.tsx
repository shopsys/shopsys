import { FC } from 'react';
import { TableStyled } from './Table.style';

/**
 * Wrapping element for html table - it gives table styling.
 */
const Table: FC = (props) => {
    return <TableStyled>{props.children}</TableStyled>;
};

/* @component */
export default Table;
