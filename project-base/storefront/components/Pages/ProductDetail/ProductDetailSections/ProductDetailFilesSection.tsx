import { DownloadIcon } from 'components/Basic/Icon/DownloadIcon';
import { ExternalLinkIcon } from 'components/Basic/Icon/ExternalLinkIcon';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { RefObject } from 'react';
import { formatBytes } from 'utils/formaters/formatBytes';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

type ProductDetailFilesSectionProps = {
    files: TypeFileFragment[];
    sectionRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailFilesSection = ({ files, sectionRef }: ProductDetailFilesSectionProps) => {
    const { t, lang } = useTranslation();
    const downloadTooltipLabel = t('Download');
    const previewTooltipLabel = t('Open in new tab');

    const formatFileSize = (size: number): string => {
        const [rawAmount, unit = ''] = formatBytes(size).split(' ');
        const amount = Number(rawAmount);
        const localizedAmount = Number.isNaN(amount)
            ? rawAmount
            : Intl.NumberFormat(lang, { maximumFractionDigits: 1 }).format(amount);

        return `${localizedAmount} ${unit}`.trim();
    };

    const getFileInfo = (file: TypeFileFragment): string | null => {
        const fileSize = file.filesize !== null ? formatFileSize(file.filesize) : null;
        const fileExtension = file.extension ? file.extension.toUpperCase() : null;

        if (fileSize === null && fileExtension === null) {
            return null;
        }

        return [fileSize, fileExtension].filter(Boolean).join(' ');
    };

    return (
        <div
            className="scroll-mt-fixed-header-with-navigation"
            data-tid={`${TIDs.product_detail_section_}${PRODUCT_DETAIL_SECTIONS_IDS.files}`}
            id={PRODUCT_DETAIL_SECTIONS_IDS.files}
            ref={sectionRef}
        >
            <Webline width="vl">
                <ProductDetailSectionHeading>{t('Files')}</ProductDetailSectionHeading>

                <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {files.map((file) => {
                        const fileInfo = getFileInfo(file);
                        const accessibleFileName = fileInfo ? `${file.anchorText} (${fileInfo})` : file.anchorText;
                        const downloadAccessibleLabel = t('Download {{file}}', {
                            ns: 'accessibility',
                            file: accessibleFileName,
                        });
                        const previewAccessibleLabel = t('View {{file}}', {
                            ns: 'accessibility',
                            file: accessibleFileName,
                        });

                        return (
                            <li
                                key={file.url}
                                className="flex items-center justify-between gap-5 rounded-xl bg-background-more px-5 py-2.5"
                            >
                                <div>
                                    {file.viewUrl ? (
                                        <Tooltip label={previewTooltipLabel}>
                                            <a
                                                aria-label={previewAccessibleLabel}
                                                className="h4 no-underline"
                                                href={file.viewUrl}
                                                rel="noopener noreferrer"
                                                target="_blank"
                                            >
                                                {file.anchorText}

                                                <ExternalLinkIcon
                                                    className="ml-1.5 size-4 -align-1"
                                                    strokeWidth="2.5"
                                                />
                                            </a>
                                        </Tooltip>
                                    ) : (
                                        <span className="h4">{file.anchorText}</span>
                                    )}

                                    {fileInfo && <p>{fileInfo}</p>}
                                </div>

                                <Tooltip label={downloadTooltipLabel}>
                                    <a
                                        aria-label={downloadAccessibleLabel}
                                        href={file.url}
                                        rel="noopener noreferrer"
                                        target="_blank"
                                    >
                                        <DownloadIcon className="size-6" />
                                    </a>
                                </Tooltip>
                            </li>
                        );
                    })}
                </ul>
            </Webline>
        </div>
    );
};
