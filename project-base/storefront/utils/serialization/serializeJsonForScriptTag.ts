const SCRIPT_TAG_ESCAPE_CHARACTER_MAP = {
    '&': '\\u0026',
    '>': '\\u003e',
    '<': '\\u003c',
    '\u2028': '\\u2028',
    '\u2029': '\\u2029',
} as const;

type ScriptTagEscapeCharacter = keyof typeof SCRIPT_TAG_ESCAPE_CHARACTER_MAP;

/**
 * Serializes JSON for embedding as raw text inside a script tag. The output is not safe for HTML attributes.
 */
export const serializeJsonForScriptTag = (data: unknown): string => {
    const serializedData = JSON.stringify(data);

    if (serializedData === undefined) {
        throw new TypeError('The provided value cannot be serialized to JSON.');
    }

    return serializedData.replace(
        /[&><\u2028\u2029]/g,
        (character) => SCRIPT_TAG_ESCAPE_CHARACTER_MAP[character as ScriptTagEscapeCharacter],
    );
};
