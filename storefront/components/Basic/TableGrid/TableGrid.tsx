import { TableGridRootStyled } from './TableGrid.style';

const TEST_IDENTIFIER = 'basic-tablegrid';

export const TableGrid: FC = ({ children }) => (
    <div className="mb-6 overflow-x-auto rounded-xl border-2 border-border" data-testid={TEST_IDENTIFIER}>
        <TableGridRootStyled>{children}</TableGridRootStyled>
    </div>
);
