import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { TableBlockData } from 'types/contentEditor';

// the table look (including the `secondary` variant) lives in `.user-text table` in styles/user-text.css
export const TableBlock: FC<BlockProps<TableBlockData>> = ({ block }) => (
    <div className="user-text my-8 max-w-full overflow-x-auto lg:my-10" data-tid={TIDs.content_block_table}>
        <table className={block.data.variant === 'secondary' ? 'secondary' : undefined}>
            <tbody>
                {block.data.cells.map((row, rowIndex) => (
                    <tr key={rowIndex}>
                        {row.map((cell, cellIndex) => (
                            <td key={cellIndex} dangerouslySetInnerHTML={{ __html: cell }} />
                        ))}
                    </tr>
                ))}
            </tbody>
        </table>
    </div>
);
