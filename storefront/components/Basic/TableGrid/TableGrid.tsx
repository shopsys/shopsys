import { TableGridRootStyled, TableGridStyled } from './TableGrid.style';
import { FC } from 'react';

const TEST_IDENTIFIER = 'basic-tablegrid';

export const TableGrid: FC = ({ children }) => (
    <TableGridStyled data-testid={TEST_IDENTIFIER}>
        <TableGridRootStyled>{children}</TableGridRootStyled>
    </TableGridStyled>
);
