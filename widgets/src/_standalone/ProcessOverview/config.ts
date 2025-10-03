import ProcessOverview from './index.svelte';

import type { TargetEmbeddedWindow } from 'svelte-standalone';
import type { Config } from './types';

declare global {
	interface Window extends TargetEmbeddedWindow<typeof ProcessOverview, 'ProcessOverview'> {}
}

export const config: Config = (() => {
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

export const t = (text: string) => config.messages?.[text] ?? text + ' (missing translation)';
