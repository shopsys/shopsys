import { DownloadIcon } from 'components/Basic/Icon/DownloadIcon';
import { ExternalLinkIcon } from 'components/Basic/Icon/ExternalLinkIcon';
import { Webline } from 'components/Layout/Webline/Webline';
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
            className="scroll-mt-[calc(var(--sticky-navigation-offset,0)+5rem)]"
            id={PRODUCT_DETAIL_SECTIONS_IDS.files}
            ref={sectionRef}
        >
            <Webline>
                <ProductDetailSectionHeading>{t('Files')}</ProductDetailSectionHeading>

                <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {files.map((file) => {
                        const fileInfo = getFileInfo(file);

                        return (
                            <li
                                key={file.url}
                                className="flex items-center justify-between gap-5 rounded-xl bg-background-more px-5 py-2.5"
                            >
                                <div>
                                    {file.viewUrl ? (
                                        <a
                                            className="h4 no-underline"
                                            href={file.viewUrl}
                                            rel="noopener noreferrer"
                                            target="_blank"
                                            aria-label={t('View {{file}}', {
                                                ns: 'accessibility',
                                                file: file.anchorText,
                                            })}
                                        >
                                            {file.anchorText}

                                            <ExternalLinkIcon className="mb-1 ml-1 size-4" />
                                        </a>
                                    ) : (
                                        <span className="h4">{file.anchorText}</span>
                                    )}

                                    {fileInfo && <p>{fileInfo}</p>}
                                </div>

                                <a
                                    href={file.url}
                                    rel="noopener noreferrer"
                                    target="_blank"
                                    aria-label={t('Download {{file}}', {
                                        ns: 'accessibility',
                                        file: file.anchorText,
                                    })}
                                >
                                    <DownloadIcon className="size-6" />
                                </a>
                            </li>
                        );
                    })}
                </ul>
            </Webline>
        </div>
    );
};
