import ProcessOverview from './index.svelte';

import type { TargetEmbeddedWindow } from 'svelte-standalone';
import type { OverviewConfig } from '../../lib/types';

declare global {
	interface Window extends TargetEmbeddedWindow<typeof ProcessOverview, 'ProcessOverview'> {}
}

export const config: OverviewConfig = (() => {
	const el = document.querySelector('[data-rpa-process-overview-config]') as HTMLElement | null;
	if (el !== null) {
		try {
			const configString = el.dataset.rpaProcessOverviewConfig;
			if (configString) {
				return JSON.parse(configString);
			}
		} catch (error) {
			// Ignore all errors.
		}
	}
	return {};
})();

export const t = (text: string, placeholders: object = {}) => {
	const translated = config.messages?.[text] ?? text + ' (missing translation)';

	// Replace `{key}` with `placeholders[key]`. If `key` is not defined, return the placeholder (incl. `{}`).
	return translated.replace(/\{([^\}]+)\}/g, (match, key) => {
		return placeholders[key] ?? match;
	});
};
