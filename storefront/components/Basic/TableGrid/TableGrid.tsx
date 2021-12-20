import { TableGridRootStyled, TableGridStyled } from './TableGrid.style';
import { FC } from 'react';

/**
 * Wrapping element for html table - it gives table styling.
 */
const TableGrid: FC = (props) => {
    const testIdentifier = 'basic-tablegrid';

    return (
        <TableGridStyled data-testid={testIdentifier}>
            <TableGridRootStyled>{props.children}</TableGridRootStyled>
        </TableGridStyled>
    );
};

/* @component */
export default TableGrid;
