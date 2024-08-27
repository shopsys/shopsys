export function formatBytes(bytes: number, decimals = 1): string {
    if (bytes === 0) {
        return '0 Bytes';
    }

    const k = 1000;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    const formattedBytes = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));

    return `${formattedBytes} ${sizes[i]}`;
}
