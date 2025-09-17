import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [sveltekit()],
	// Houston, we have a problem (https://en.wikipedia.org/wiki/Houston,_we_have_a_problem)
	//
	// Error during handleBuild: [vite]: Rollup failed to resolve import "$app/navigation" from "/app/assets/widgets/src/_standalone/ProcessOverview/index.svelte".
	// This is most likely unintended because it can break your application at runtime.
	// If you do want to externalize this module explicitly add it to
	// `build.rollupOptions.external`
	//
	// https://rollupjs.org/configuration-options/#external
	build: {
		rollupOptions: {
			external: [
				'$app/state',
			]
		}
	}
});
