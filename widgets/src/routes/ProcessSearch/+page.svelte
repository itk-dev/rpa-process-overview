<script lang="ts">
	import { onMount } from 'svelte';

	onMount(() => {
		const script = document.createElement('script');
		script.src = '/src/_standalone/ProcessSearch/embed.ts';
		script.async = true;
		script.type = 'module';

		document.head.appendChild(script);

		return () => {
			document.head.removeChild(script);
			window.ProcessSearch.stop();
		};
	});

	const config = {
		search_url: '/api/search'
	};
</script>

<div id="ProcessSearch" data-config={JSON.stringify(config)}></div>
