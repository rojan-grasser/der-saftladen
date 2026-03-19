import axios from 'axios';
import { reactive } from 'vue';

import { FileWithPreview } from '@/composables/useFileUpload';
import { FileUpload } from '@/pages/forum/types';

import forumFiles from '@/routes/forum/files';

/**
 * Reactive map of file id → upload progress (0–100).
 * A file is present in this map only while it is being uploaded.
 */
export const uploadProgress = reactive<Record<string, number>>({});

export const uploadFiles = async (
    files: FileWithPreview[],
    uploadedFiles: Array<{ beId: string; appId: string }>,
    professionId: number,
    topicId: number,
    open: boolean,
) => {
    if (!open) {
        return;
    }

    const removedFiles = uploadedFiles.filter(
        (f) => !files.find((fi) => fi.id === f.appId),
    );

    await Promise.all(
        removedFiles.map(async (file) =>
            axios.delete(
                forumFiles.remove({
                    fileId: file.beId,
                    topicId,
                    professionId,
                }).url,
            ),
        ),
    );

    removedFiles.forEach((file) => {
        const index = uploadedFiles.findIndex((f) => f.appId === file.appId);
        if (index !== -1) uploadedFiles.splice(index, 1);
    });

    await Promise.all(
        files
            .filter((file) => !uploadedFiles.find((f) => file.id === f.appId))
            .map(async (file) => {
                const formData = new FormData();

                if (file.file instanceof File) {
                    formData.append('file', file.file);

                    // Initialise progress tracking for this file
                    uploadProgress[file.id] = 0;

                    try {
                        const { data } = (await axios.post(
                            forumFiles.store({ professionId, topicId }).url,
                            formData,
                            {
                                onUploadProgress(progressEvent) {
                                    if (progressEvent.total) {
                                        uploadProgress[file.id] = Math.round(
                                            (progressEvent.loaded * 100) /
                                                progressEvent.total,
                                        );
                                    }
                                },
                            },
                        )) as { data: Omit<FileUpload, 'type'> };

                        uploadedFiles.push({ beId: data.id, appId: file.id });
                    } finally {
                        // Remove progress entry once upload completes (or fails)
                        delete uploadProgress[file.id];
                    }
                }
            }),
    );
};
