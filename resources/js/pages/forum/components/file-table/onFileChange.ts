import axios from 'axios';

import { FileWithPreview } from '@/composables/useFileUpload';
import { FileUpload } from '@/pages/forum/types';
import forumFiles from '@/routes/forum/files';

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

                    const { data } = (await axios.post(
                        forumFiles.store({ professionId, topicId }).url,
                        formData,
                    )) as { data: Omit<FileUpload, 'type'> };

                    uploadedFiles.push({ beId: data.id, appId: file.id });
                }
            }),
    );
};
