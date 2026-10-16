export const getYouTubeThumbnailUrl = (videoId: string, isHighResolution = true) =>
    `https://img.youtube.com/vi/${videoId}/${isHighResolution ? 'maxresdefault' : 'hqdefault'}.jpg`;
