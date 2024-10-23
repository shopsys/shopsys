import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import useTranslation from 'next-translate/useTranslation';
import { JSX, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Controller, Control, useController, FieldError } from 'react-hook-form';
import { formatBytes } from 'utils/formaters/formatBytes';
import { twMergeCustom } from 'utils/twMerge';

type DropzoneControlledProps = {
    control: Control<any>;
    formName: string;
    name: string;
    render: (dropzone: JSX.Element) => JSX.Element;
    label: string;
    required?: boolean;
    disabled?: boolean;
};

const DropzoneControlled: React.FC<DropzoneControlledProps> = ({
    control,
    formName,
    name,
    render,
    label,
    required = false,
    disabled = false,
}) => {
    const { t } = useTranslation();
    const dropzoneId = formName + '-' + name;
    const {
        fieldState: { error },
        field: { onChange, value },
    } = useController({ name, control });
    const [files, setFiles] = useState<File[]>([]);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop: (acceptedFiles) => {
            const updatedFiles = [...files, ...acceptedFiles];
            setFiles(updatedFiles);
            onChange(updatedFiles);
        },
        accept: {
            'image/jpeg': [],
            'image/png': [],
        },
        disabled,
    });

    const removeFile = (fileToRemove: File) => {
        const updatedFiles = files.filter((file) => file !== fileToRemove);
        setFiles(updatedFiles);
        onChange(updatedFiles);
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
        'border-2 p-10 text-center rounded-md border-dashed cursor-pointer group',
        !isDragActive && 'border-inputBorder bg-inputBackground hover:border-inputBorderHovered',
        isDragActive && 'border-inputBorderActive bg-inputBackgroundActive',
        error && 'border-inputError',
        disabled &&
            'border-inputBorderDisabled bg-inputBackgroundDisabled text-inputTextDisabled hover:border-inputBorderDisabled cursor-not-allowed',
    );
    const labelTwClass = twMergeCustom(
        'text-inputPlaceholder group-hover:text-inputPlaceholderHovered',
        isDragActive && 'text-inputPlaceholderHovered',
        disabled && 'text-inputPlaceholderDisabled group-hover:text-inputPlaceholderDisabled',
    );
    const listItemTwClass = 'flex my-1 justify-between items-center group';
    const fileNameTwClass = 'flex-1 text-gray-800 group-hover:text-linkHovered transition-colors duration-300';
    const legendTwClass = 'text-inputTextDisabled text-sm mt-2';

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
                                {required && <span className="ml-1 text-textError">*</span>}
                            </p>
                        </div>
                        <p className={legendTwClass}>
                            {t(
                                'Please attach JPG or PNG images of the claimed goods with a maximum file size of {{ max }}. Maximum files count is {{ maxFilesCount }}.',
                                {
                                    max: formatBytes(VALIDATION_CONSTANTS.fileMaxSize),
                                    maxFilesCount: VALIDATION_CONSTANTS.maxFilesCount,
                                },
                            )}
                        </p>
                        {error && formatError(error)}
                        {value && value.length > 0 && (
                            <ul className="mt-2">
                                {value.map((file: File, index: number) => (
                                    <li key={index} className={listItemTwClass}>
                                        <span className={fileNameTwClass}>
                                            {file.name} - {formatBytes(file.size)}
                                        </span>
                                        <Button
                                            className="p-1"
                                            isDisabled={disabled}
                                            variant="inverted"
                                            onClick={() => removeFile(file)}
                                        >
                                            <RemoveIcon className="w-2" />
                                        </Button>
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

export default DropzoneControlled;
