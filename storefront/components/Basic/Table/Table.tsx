import { TableStyled } from './Table.style';
import { FC } from 'react';

const Table: FC = (props) => {
    const testIdentifier = 'basic-table';

    return <TableStyled data-testid={testIdentifier}>{props.children}</TableStyled>;
};

export default Table;
