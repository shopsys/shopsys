type DomStabilityOptions = {
    quietPeriod?: number;
    timeout?: number;
};

// A quiet DOM supplements observable readiness; it cannot detect future timers or requests.
export const waitForDomStability = (
    doc: Document,
    { quietPeriod = 500, timeout = 20000 }: DomStabilityOptions = {},
): Promise<void> =>
    new Promise((resolve, reject) => {
        let quietTimer: ReturnType<typeof setTimeout>;
        const observer = new MutationObserver(() => {
            clearTimeout(quietTimer);
            quietTimer = setTimeout(() => finish(), quietPeriod);
        });
        const finish = (error?: Error) => {
            observer.disconnect();
            clearTimeout(quietTimer);
            clearTimeout(deadline);
            doc.defaultView?.removeEventListener('pagehide', onPageHide);
            if (error) {
                reject(error);
            } else {
                resolve();
            }
        };
        const onPageHide = () => finish(new Error('The page changed while waiting for a stable DOM'));
        const deadline = setTimeout(() => finish(new Error(`DOM did not settle within ${timeout}ms`)), timeout);

        observer.observe(doc, { subtree: true, childList: true, attributes: true, characterData: true });
        doc.defaultView?.addEventListener('pagehide', onPageHide);
        quietTimer = setTimeout(() => finish(), quietPeriod);
    });
