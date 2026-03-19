import {
    LucideFile,
    LucideFileArchive,
    LucideFileSpreadsheet,
    LucideFileText,
    LucideHeadphones,
    LucideImage,
    LucideVideo,
} from 'lucide-vue-next';

export const getFileIcon = (file: { file: File | { type: string; name: string } }) => {
    const fileType =
        file.file instanceof File ? file.file.type : file.file.type;
    const fileName =
        file.file instanceof File ? file.file.name : file.file.name;

    if (
        fileType.includes('pdf') ||
        fileName.endsWith('.pdf') ||
        fileType.includes('word') ||
        fileName.endsWith('.doc') ||
        fileName.endsWith('.docx')
    ) {
        return LucideFileText;
    } else if (
        fileType.includes('zip') ||
        fileType.includes('archive') ||
        fileName.endsWith('.zip') ||
        fileName.endsWith('.rar')
    ) {
        return LucideFileArchive;
    } else if (
        fileType.includes('excel') ||
        fileName.endsWith('.xls') ||
        fileName.endsWith('.xlsx')
    ) {
        return LucideFileSpreadsheet;
    } else if (fileType.includes('video/')) {
        return LucideVideo;
    } else if (fileType.includes('audio/')) {
        return LucideHeadphones;
    } else if (fileType.startsWith('image/')) {
        return LucideImage;
    }
    return LucideFile;
};
