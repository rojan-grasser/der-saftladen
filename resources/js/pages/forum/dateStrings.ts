export const formatDate = (dateString: string) => {
    const date = new Date(dateString);

    return `${date.getUTCDate()}.${date.getUTCMonth() + 1}.${date.getUTCFullYear()}`;
};

export const formatTime = (dateString: string) => {
    const date = new Date(dateString);

    return `${date.getUTCHours().toString().padStart(2, '0')}:${date.getUTCMinutes().toString().padStart(2, '0')}`;
};
