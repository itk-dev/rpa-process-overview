import ProcessSearch from './index.svelte';

import type { TargetEmbeddedWindow } from 'svelte-standalone';
import type { SearchConfig } from '$lib/types';

declare global {
	interface Window extends TargetEmbeddedWindow<typeof ProcessSearch, 'ProcessSearch'> {}
}

export const config: SearchConfig = (() => {
	const el = document.querySelector('[data-rpa-process-search-config]') as HTMLElement | null;
	if (el !== null) {
		try {
			const configString = el.dataset.rpaProcessSearchConfig;
			if (configString) {
				return JSON.parse(configString);
			}
		} catch (error) {
			// Ignore all errors.
		}
	}
	return {};
})();

export const t = (text: string) => config.messages?.[text] ?? text + ' (missing translation)';
