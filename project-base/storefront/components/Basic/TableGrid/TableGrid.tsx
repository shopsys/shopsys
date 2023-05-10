import styles from './TableGrid.module.sass';

const TEST_IDENTIFIER = 'basic-tablegrid';

export const TableGrid: FC = ({ children }) => (
    <div className="mb-6 overflow-x-auto rounded-xl border-2 border-border" data-testid={TEST_IDENTIFIER}>
        <table className={styles.tableGridRoot}>{children}</table>
    </div>
);

export const TableGridColumn: FC = ({ children }) => <table className={styles.tableGridColumn}>{children}</table>;
