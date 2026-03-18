import axios from 'axios';

import { FileWithPreview } from '@/composables/useFileUpload';
import { FileUpload } from '@/pages/forum/types';
import forumFiles from '@/routes/forum/files';

export const uploadFiles = async (
    files: FileWithPreview[],
    uploadedFiles: Array<{ beId: string; appId: string }>,
    professionId: number,
) => {
    await Promise.all(
        files
            .filter((file) => !uploadedFiles.find((f) => file.id === f.appId))
            .map(async (file) => {
                const formData = new FormData();

                if (file.file instanceof File) {
                    formData.append('file', file.file);

                    const { data } = (await axios.post(
                        forumFiles.store({ professionId }).url,
                        formData,
                    )) as { data: Omit<FileUpload, 'type'> };

                    uploadedFiles.push({ beId: data.id, appId: file.id });
                }
            }),
    );

    return files.map(
        (f) => uploadedFiles.find((uf) => uf.appId === f.id)!.beId,
    );
};
