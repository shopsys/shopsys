import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { Image } from 'components/Basic/Image/Image';
import { IconButton } from 'components/Forms/Button/IconButton';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { ReactElement, useEffect, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Control, Controller, FieldError, FieldPath, FieldValues, useController } from 'react-hook-form';
import { FunctionComponentProps } from 'types/globals';
import { formatBytes } from 'utils/formaters/formatBytes';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type DropzoneControlledProps<TFieldValues extends FieldValues, TTransformedValues> = {
    control: Control<TFieldValues, any, TTransformedValues>;
    formName: string;
    name: FieldPath<TFieldValues>;
    render: (dropzone: ReactElement) => ReactElement;
    label: string;
    required?: boolean;
    disabled?: boolean;
    legend?: string;
    showPreviews?: boolean;
};

const PREVIEW_DIMENSION = 160;

const FilePreview: FC<{ file: File }> = ({ file }) => {
    const [previewUrl, setPreviewUrl] = useState<string>();

    useEffect(() => {
        let isCancelled = false;

        // the CSP allows data: but not blob:, so a small thumbnail is drawn
        // on a canvas instead of pointing the img to an object URL of the file
        createImageBitmap(file)
            .then((bitmap) => {
                if (isCancelled) {
                    bitmap.close();

                    return;
                }

                const scale = Math.min(PREVIEW_DIMENSION / Math.max(bitmap.width, bitmap.height), 1);
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(bitmap.width * scale);
                canvas.height = Math.round(bitmap.height * scale);

                const context = canvas.getContext('2d');
                if (context) {
                    context.fillStyle = '#fff';
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                    setPreviewUrl(canvas.toDataURL('image/jpeg', 0.8));
                }

                bitmap.close();
            })
            .catch(() => undefined); // a file that cannot be decoded simply has no preview

        return () => {
            isCancelled = true;
        };
    }, [file]);

    return (
        <Image alt={file.name} className="size-20 rounded-md object-cover" height={80} src={previewUrl} width={80} />
    );
};

export const DropzoneControlled = <TFieldValues extends FieldValues, TTransformedValues = TFieldValues>({
    control,
    formName,
    name,
    render,
    label,
    required = false,
    disabled = false,
    legend,
    showPreviews = false,
}: DropzoneControlledProps<TFieldValues, TTransformedValues> & FunctionComponentProps) => {
    const { t } = useTranslation();
    const dropzoneId = `${formName}-${name}`;
    const {
        fieldState: { error },
        field: { onChange, value },
    } = useController({ name, control });
    const [, setFiles] = useState<File[]>([]);

    const isFileAlreadySelected = (selectedFiles: File[], file: File) =>
        selectedFiles.some(
            (selectedFile) =>
                selectedFile.name === file.name &&
                selectedFile.size === file.size &&
                selectedFile.lastModified === file.lastModified,
        );

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop: (acceptedFiles) => {
            setFiles((currentFiles) => {
                const updatedFiles = [...currentFiles];

                for (const acceptedFile of acceptedFiles) {
                    if (!isFileAlreadySelected(updatedFiles, acceptedFile)) {
                        updatedFiles.push(acceptedFile);
                    }
                }

                onChange(updatedFiles);

                return updatedFiles;
            });
        },
        accept: {
            'image/jpeg': [],
            'image/png': [],
        },
        disabled,
    });

    const removeFile = (fileToRemove: File) => {
        setFiles((currentFiles) => {
            const updatedFiles = currentFiles.filter((file) => file !== fileToRemove);

            onChange(updatedFiles);

            return updatedFiles;
        });
    };

    const formatError = (error: FieldError) => {
        if (Array.isArray(error)) {
            return error.map((e, index) => (
                <div key={index}>
                    <FormLineError error={e} inputType="dropzone" />
                </div>
            ));
        }

        return <FormLineError error={error} inputType="dropzone" />;
    };

    const wrapperTwClass = twMergeCustom(
        'group cursor-pointer rounded-md border-2 border-dashed p-10 text-center',
        !isDragActive && 'border-input-border-default bg-input-bg-default hover:border-input-border-hovered',
        isDragActive && 'border-input-border-active bg-input-fill',
        error && 'border-input-border-error',
        disabled &&
            'cursor-not-allowed border-input-border-disabled bg-input-bg-disabled text-input-text-disabled hover:border-input-border-disabled',
    );
    const labelTwClass = twMergeCustom(
        'text-input-placeholder-default group-hover:text-input-placeholder-hovered',
        isDragActive && 'text-input-placeholder-hovered',
        disabled && 'text-input-placeholder-disabled group-hover:text-input-placeholder-disabled',
    );
    const listItemTwClass = 'flex my-1 justify-between items-center group';
    const fileNameTwClass = 'flex-1 text-gray-800 group-hover:text-link-hovered transition-colors duration-300';
    const legendTwClass = 'text-input-text-disabled text-sm mt-2';
    const legendText =
        legend ??
        t(
            'Please attach JPG or PNG images of the claimed goods with a maximum file size of {{ max }}. Maximum files count is {{ maxFilesCount }}.',
            {
                max: formatBytes(VALIDATION_CONSTANTS.fileMaxSize),
                maxFilesCount: VALIDATION_CONSTANTS.maxFilesCount,
            },
        );

    return (
        <Controller
            control={control}
            name={name}
            render={() => {
                return render(
                    <>
                        <div id={dropzoneId} {...getRootProps({ className: wrapperTwClass })}>
                            <input {...getInputProps()} />
                            <p className={labelTwClass}>
                                {label}
                                {required && <span className="ml-1 text-text-error">*</span>}
                            </p>
                        </div>
                        {legendText && <p className={legendTwClass}>{legendText}</p>}
                        {error && formatError(error)}
                        {value && value.length > 0 && showPreviews && (
                            <ul className="mt-2 flex flex-wrap gap-3">
                                {value.map((file: File, index: number) => (
                                    <li key={`${file.name}-${index}`} className="flex flex-col items-center gap-1">
                                        <FilePreview file={file} />

                                        <IconButton
                                            Icon={TrashCanIcon}
                                            disabled={disabled}
                                            title={t('Remove file')}
                                            onClick={() => removeFile(file)}
                                        />
                                    </li>
                                ))}
                            </ul>
                        )}
                        {value && value.length > 0 && !showPreviews && (
                            <ul className="mt-2">
                                {value.map((file: File, index: number) => (
                                    <li key={`${file.name}-${index}`} className={listItemTwClass}>
                                        <span className={fileNameTwClass}>
                                            {file.name} - {formatBytes(file.size)}
                                        </span>

                                        <IconButton
                                            Icon={TrashCanIcon}
                                            ariaLabel={t('Remove file')}
                                            disabled={disabled}
                                            shape="rounded"
                                            size="small"
                                            title={t('Remove file')}
                                            tooltipLabel={t('Remove file')}
                                            variant="ghost"
                                            onClick={() => removeFile(file)}
                                        />
                                    </li>
                                ))}
                            </ul>
                        )}
                    </>,
                );
            }}
        />
    );
};
