const storageKey = 'admin-flash-message-history';
const maxEntries = 20;

export const flashMessageHistoryChangedEvent = 'admin:flash-message-history-changed';

export function getFlashMessageHistory() {
    try {
        const entries = JSON.parse(window.sessionStorage.getItem(storageKey));

        return Array.isArray(entries) ? entries : [];
    } catch {
        return [];
    }
}

export function addFlashMessageToHistory(bgClass, messageHtml) {
    const entries = [{ bgClass, messageHtml, createdAt: Date.now() }, ...getFlashMessageHistory()];

    saveFlashMessageHistory(entries.slice(0, maxEntries));
}

export function clearFlashMessageHistory() {
    saveFlashMessageHistory([]);
}

function saveFlashMessageHistory(entries) {
    try {
        window.sessionStorage.setItem(storageKey, JSON.stringify(entries));
    } catch {
        return;
    }

    document.dispatchEvent(new CustomEvent(flashMessageHistoryChangedEvent));
}
