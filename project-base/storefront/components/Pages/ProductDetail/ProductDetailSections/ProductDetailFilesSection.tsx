import { DownloadIcon } from 'components/Basic/Icon/DownloadIcon';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

type ProductDetailFilesSectionProps = {
    files: TypeFileFragment[];
    sectionRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailFilesSection = ({ files, sectionRef }: ProductDetailFilesSectionProps) => {
    const { t } = useTranslation();

    return (
        <div className="scroll-mt-20" id={PRODUCT_DETAIL_SECTIONS_IDS.files} ref={sectionRef}>
            <Webline>
                <ProductDetailSectionHeading>{t('Files')}</ProductDetailSectionHeading>

                <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {files.map((file) => (
                        <li key={file.url}>
                            <a
                                className="flex cursor-pointer items-center gap-5 rounded-xl bg-background-more px-5 py-2.5 no-underline"
                                href={file.url}
                                aria-label={t('Download {{file}}', {
                                    ns: 'accessibility',
                                    file: file.anchorText,
                                })}
                            >
                                <DownloadIcon className="size-6" />

                                <span className="h4">{file.anchorText}</span>
                            </a>
                        </li>
                    ))}
                </ul>
            </Webline>
        </div>
    );
};
