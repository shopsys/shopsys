const TEST_IDENTIFIER = 'basic-table';

export const Table: FC = ({ children }) => (
    <table className="w-full" data-testid={TEST_IDENTIFIER}>
        {children}
    </table>
);
