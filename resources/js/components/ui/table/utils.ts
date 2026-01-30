import type { Ref } from 'vue';

type Updater<T> = T | ((oldValue: T) => T);

const isFunction = (
  value: unknown,
): value is (...args: unknown[]) => unknown => typeof value === 'function';

export function valueUpdater<T>(updaterOrValue: Updater<T>, ref: Ref<T>) {
  ref.value = isFunction(updaterOrValue)
    ? updaterOrValue(ref.value)
    : updaterOrValue
}
