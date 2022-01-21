import { FC } from 'react';
import { TableStyled } from './Table.style';

/**
 * Wrapping element for html table - it gives table styling.
 */
const Table: FC = (props) => {
    const testIdentifier = 'basic-table';

    return <TableStyled data-testid={testIdentifier}>{props.children}</TableStyled>;
};

/* @component */
export default Table;
