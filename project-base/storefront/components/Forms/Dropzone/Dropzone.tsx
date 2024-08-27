import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { useState } from 'react';
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
};

const DropzoneControlled: React.FC<DropzoneControlledProps> = ({ control, formName, name, render, label }) => {
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
        accept: { 'image/*': [] },
    });

    const removeFile = (fileToRemove: File) => {
        const updatedFiles = files.filter((file) => file !== fileToRemove);
        setFiles(updatedFiles);
        onChange(updatedFiles);
    };

    const classNames = twMergeCustom(
        'border-2 p-10 text-center rounded-md border-dashed cursor-pointer',
        !isDragActive && 'border-inputBorder bg-inputBackground hover:border-inputBorderHovered',
        isDragActive && 'border-inputBorderActive bg-inputBackgroundActive',
        error && 'border-inputError',
    );

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

    return (
        <Controller
            control={control}
            name={name}
            render={() => {
                return render(
                    <>
                        <div id={dropzoneId} {...getRootProps({ className: classNames })}>
                            <input {...getInputProps()} />
                            <p
                                className={twMergeCustom(
                                    'text-inputPlaceholder hover:text-inputPlaceholderHovered',
                                    isDragActive && 'text-inputPlaceholderHovered',
                                )}
                            >
                                {label}
                            </p>
                        </div>
                        {error && formatError(error)}
                        {value && value.length > 0 && (
                            <ul className="mt-2">
                                {value.map((file: File, index: number) => (
                                    <li key={index} className="flex my-1 justify-between items-center group">
                                        <span className="flex-1 text-gray-800 group-hover:text-linkHovered transition-colors duration-300">
                                            {file.name} - {formatBytes(file.size)}
                                        </span>
                                        <Button className="p-1" variant="inverted" onClick={() => removeFile(file)}>
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
