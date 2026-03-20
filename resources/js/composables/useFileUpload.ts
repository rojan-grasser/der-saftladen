import {
    computed,
    type MaybeRefOrGetter,
    ref,
    shallowRef,
    toValue,
    watch,
} from 'vue';

export type FileMetadata = {
    name: string;
    size: number;
    type: string;
    url: string;
    id: string;
};

export type FileWithPreview = {
    file: File | FileMetadata;
    id: string;
    preview?: string;
};

export type FileUploadOptions = {
    maxFiles?: number;
    maxSize?: number;
    accept?: string;
    multiple?: boolean;
    initialFiles?: MaybeRefOrGetter<FileMetadata[]>;
    onFilesChange?: (files: FileWithPreview[]) => void;
    onFilesAdded?: (files: FileWithPreview[]) => void;
    onFileRemoved?: (id: string) => void;
};

export const useFileUpload = (options: FileUploadOptions = {}) => {
    const {
        maxFiles = Infinity,
        maxSize = Infinity,
        accept = '*',
        multiple = false,
        initialFiles = [],
        onFilesChange,
        onFilesAdded,
        onFileRemoved,
    } = options;

    let suppressChange = false;

    const files = ref<FileWithPreview[]>(
        (toValue(initialFiles) ?? []).map((file) => ({
            file,
            id: file.id,
            preview: file.url,
        })),
    );

    watch(
        () => toValue(initialFiles),
        (newInitial) => {
            suppressChange = true;
            files.value = (newInitial ?? []).map((file) => ({
                file,
                id: file.id,
                preview: file.url,
            }));
            errors.value = [];
        },
    );

    const errors = ref<string[]>([]);

    const ariaLabel = computed(() => {
        if (files.value.length > 0) {
            return multiple ? 'Dateien ändern' : 'Datei ändern';
        }
        return multiple ? 'Dateien hochladen' : 'Datei hochladen';
    });

    const inputRef = shallowRef<HTMLInputElement | null>(null);
    const dropzoneRef = shallowRef<HTMLElement | null>(null);

    watch(inputRef, (newInput) => {
        if (!newInput) return;
        configureInput();
    });

    const configureInput = () => {
        if (!inputRef.value) return;
        inputRef.value.type = 'file';
        inputRef.value.className = 'sr-only';
        inputRef.value.accept = accept;
        inputRef.value.multiple = multiple;
        inputRef.value.addEventListener('change', handleFileChange);
    };

    watch(dropzoneRef, (newDropzone) => {
        if (!newDropzone) return;
        configureDropzone();
    });

    const configureDropzone = () => {
        if (!dropzoneRef.value) return;
        dropzoneRef.value.addEventListener('dragenter', handleDragEnter);
        dropzoneRef.value.addEventListener('dragleave', handleDragLeave);
        dropzoneRef.value.addEventListener('dragover', handleDragOver);
        dropzoneRef.value.addEventListener('drop', handleDrop);

        dropzoneRef.value.setAttribute('aria-label', ariaLabel.value);
    };

    watch(ariaLabel, (newValue) => {
        if (!dropzoneRef.value) return;
        dropzoneRef.value.setAttribute('aria-label', newValue);
    });

    const validateFile = (file: File | FileMetadata): string | null => {
        if (file instanceof File) {
            if (file.size > maxSize) {
                return `File "${file.name}" überschreitet die maximale größe von ${formatBytes(maxSize)}.`;
            }
        } else {
            if (file.size > maxSize) {
                return `File "${file.name}" überschreitet die maximale größe von ${formatBytes(maxSize)}.`;
            }
        }

        if (accept !== '*') {
            const acceptedTypes = accept.split(',').map((type) => type.trim());
            const fileType = file instanceof File ? file.type || '' : file.type;
            const fileExtension = `.${file instanceof File ? file.name.split('.').pop() : file.name.split('.').pop()}`;

            const isAccepted = acceptedTypes.some((type) => {
                if (type.startsWith('.')) {
                    return fileExtension.toLowerCase() === type.toLowerCase();
                }
                if (type.endsWith('/*')) {
                    const baseType = type.split('/')[0];
                    return fileType.startsWith(`${baseType}/`);
                }
                return fileType === type;
            });

            if (!isAccepted) {
                return `File "${file instanceof File ? file.name : file.name}" ist kein akzeptierter dateityp.`;
            }
        }

        return null;
    };

    const createPreview = (file: File | FileMetadata): string | undefined => {
        if (file instanceof File) {
            return URL.createObjectURL(file);
        }
        return file.url;
    };

    const generateUniqueId = (file: File | FileMetadata): string => {
        if (file instanceof File) {
            return `${file.name}-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
        }
        return file.id;
    };

    const removeFile = (id: string | undefined) => {
        if (!id) return;

        const fileToRemove = files.value.find((file) => file.id === id);
        if (
            fileToRemove &&
            fileToRemove.preview &&
            fileToRemove.file instanceof File &&
            fileToRemove.file.type.startsWith('image/')
        ) {
            URL.revokeObjectURL(fileToRemove.preview);
        }

        files.value = files.value.filter((file) => file.id !== id);
        errors.value = [];
        onFileRemoved?.(id);
    };

    const clearFiles = () => {
        files.value.forEach((file) => {
            if (
                file.preview &&
                file.file instanceof File &&
                file.file.type.startsWith('image/')
            ) {
                URL.revokeObjectURL(file.preview);
            }
            removeFile(file.id);
        });

        if (inputRef.value) {
            inputRef.value.value = '';
        }

        files.value = [];
        errors.value = [];
        // onFilesChange?.(files.value);
    };

    const addFiles = (newFiles: FileList | File[]) => {
        if (!newFiles || newFiles.length === 0) return;

        const newFilesArray = Array.from(newFiles);
        const newErrors: string[] = [];

        errors.value = [];

        if (!multiple) {
            clearFiles();
        }

        if (
            multiple &&
            maxFiles !== Infinity &&
            files.value.length + newFilesArray.length > maxFiles
        ) {
            newErrors.push(
                `Du kannst nur ${maxFiles} dateien hochladen.`,
            );
            errors.value = newErrors;
            return;
        }

        const validFiles: FileWithPreview[] = [];

        newFilesArray.forEach((file) => {
            const isDuplicate = files.value.some(
                (existingFile) =>
                    existingFile.file.name === file.name &&
                    existingFile.file.size === file.size,
            );

            if (isDuplicate) {
                return;
            }

            if (file.size > maxSize) {
                newErrors.push(
                    multiple
                        ? `Manche Dateien überschreiten das limit von  ${formatBytes(maxSize)}.`
                        : `Die Datei überschreitet das maximum von ${formatBytes(maxSize)}.`,
                );
                return;
            }

            const error = validateFile(file);
            if (error) {
                newErrors.push(error);
            } else {
                validFiles.push({
                    file,
                    id: generateUniqueId(file),
                    preview: createPreview(file),
                });
            }
        });

        if (validFiles.length > 0) {
            files.value = !multiple
                ? validFiles
                : [...files.value, ...validFiles];
            errors.value = newErrors;
            onFilesAdded?.(validFiles);
        } else if (newErrors.length > 0) {
            errors.value = newErrors;
        }

        if (inputRef.value) {
            inputRef.value.value = '';
        }
    };

    const clearErrors = () => {
        errors.value = [];
    };

    const handleDragEnter = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (dropzoneRef.value) {
            dropzoneRef.value.setAttribute('data-dragging', 'true');
        }
    };

    const handleDragLeave = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (
            e.currentTarget &&
            e.relatedTarget &&
            (e.currentTarget as HTMLElement).contains(e.relatedTarget as Node)
        ) {
            return;
        }

        if (dropzoneRef.value) {
            dropzoneRef.value.removeAttribute('data-dragging');
        }
    };

    const handleDragOver = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const handleDrop = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (dropzoneRef.value) {
            dropzoneRef.value.removeAttribute('data-dragging');
        }

        if (inputRef.value?.disabled) {
            return;
        }

        if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
            if (!multiple) {
                const file = e.dataTransfer.files[0];
                if (file) {
                    addFiles([file]);
                }
            } else {
                addFiles(e.dataTransfer.files);
            }
        }
    };

    const handleFileChange = (e: Event) => {
        const target = e.target as HTMLInputElement;
        if (target.files && target.files.length > 0) {
            addFiles(target.files);
        }
    };

    const openFileDialog = () => {
        if (inputRef.value) {
            inputRef.value.click();
        }
    };

    watch(
        files,
        (newFiles) => {
            if (suppressChange) {
                suppressChange = false;
                return;
            }
            onFilesChange?.(newFiles);
        },
        { deep: true },
    );

    return {
        files,
        errors,
        addFiles,
        removeFile,
        clearFiles,
        clearErrors,
        handleFileChange,
        openFileDialog,
        onFilesAdded,
        inputRef,
        dropzoneRef,
    };
};

export const formatBytes = (bytes: number, decimals = 2): string => {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return (
        Number.parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) +
        (sizes[i] || '')
    );
};
