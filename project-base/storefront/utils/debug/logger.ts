/**
 * Debug logger with module-specific toggles
 * Set individual flags to true/false to enable/disable logging per module
 */
const DEBUG_FLAGS = {
    INIT_SERVER_SIDE_PROPS: true,
    DOMAIN_CONFIG: false,
    GLOBAL: false, // fallback for modules without specific flags
};

const createModuleLogger = (module: keyof typeof DEBUG_FLAGS) => ({
    log: (...args: any[]) => {
        if (DEBUG_FLAGS[module]) {
            console.log(...args);
        }
    },
    warn: (...args: any[]) => {
        if (DEBUG_FLAGS[module]) {
            console.warn(...args);
        }
    },
    error: (...args: any[]) => {
        if (DEBUG_FLAGS[module]) {
            console.error(...args);
        }
    },
});

// Module-specific loggers
export const initServerSidePropsDebug = createModuleLogger('INIT_SERVER_SIDE_PROPS');
export const domainConfigDebug = createModuleLogger('DOMAIN_CONFIG');

// Legacy global logger (fallback)
export const debugLog = (...args: any[]) => {
    if (DEBUG_FLAGS.GLOBAL) {
        console.log(...args);
    }
};

export const debugWarn = (...args: any[]) => {
    if (DEBUG_FLAGS.GLOBAL) {
        console.warn(...args);
    }
};

export const debugError = (...args: any[]) => {
    if (DEBUG_FLAGS.GLOBAL) {
        console.error(...args);
    }
};
