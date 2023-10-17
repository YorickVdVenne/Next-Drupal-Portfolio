export function hasValue<T> (v: T | undefined | null): v is T {
    return (v !== null && v !== undefined)
}