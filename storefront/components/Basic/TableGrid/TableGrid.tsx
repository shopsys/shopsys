import { TableGridRootStyled, TableGridStyled } from './TableGrid.style';
import { FC } from 'react';

export const TableGrid: FC = (props) => {
    const testIdentifier = 'basic-tablegrid';

    return (
        <TableGridStyled data-testid={testIdentifier}>
            <TableGridRootStyled>{props.children}</TableGridRootStyled>
        </TableGridStyled>
    );
};
