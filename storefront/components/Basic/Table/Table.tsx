import { TableStyled } from './Table.style';
import { FC } from 'react';

/**
 * Wrapping element for html table - it gives table styling.
 */
const Table: FC = (props) => {
    const testIdentifier = 'basic-table';

    return <TableStyled data-testid={testIdentifier}>{props.children}</TableStyled>;
};

/* @component */
export default Table;
