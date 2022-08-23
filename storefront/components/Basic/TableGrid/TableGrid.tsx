import { TableGridRootStyled, TableGridStyled } from './TableGrid.style';
import { FC } from 'react';

const TableGrid: FC = (props) => {
    const testIdentifier = 'basic-tablegrid';

    return (
        <TableGridStyled data-testid={testIdentifier}>
            <TableGridRootStyled>{props.children}</TableGridRootStyled>
        </TableGridStyled>
    );
};

export default TableGrid;
