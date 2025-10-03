import ProcessSearch from './index.svelte';

import type { TargetEmbeddedWindow } from 'svelte-standalone';

declare global {
	interface Window extends TargetEmbeddedWindow<typeof ProcessSearch, 'ProcessSearch'> {}
}
