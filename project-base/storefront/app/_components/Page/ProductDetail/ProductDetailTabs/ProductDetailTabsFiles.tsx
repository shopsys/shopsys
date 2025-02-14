import { DownloadIcon } from 'components/Basic/Icon/DownloadIcon';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.ssr';

export type ProductDetailTabsFilesProps = {
    files: TypeFileFragment[];
};

export const ProductDetailTabsFiles: FC<ProductDetailTabsFilesProps> = ({ files }) => {
    if (!files.length) {
        return null;
    }

    return (
        <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2">
            {files.map((file) => (
                <li key={file.url} className="">
                    <a
                        className="flex cursor-pointer items-center gap-5 rounded-xl bg-backgroundMore px-5 py-2.5 no-underline"
                        href={file.url}
                    >
                        <DownloadIcon className="size-6" />
                        <h4>{file.anchorText}</h4>
                    </a>
                </li>
            ))}
        </ul>
    );
};
