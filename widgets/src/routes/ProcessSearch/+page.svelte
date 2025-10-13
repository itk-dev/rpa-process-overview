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
		search_url: 'https://rpa-process-overview.local.itkdev.dk/group/1/overview/1/search',
		minimum_search_query_length: 3,
	};
</script>

<div id="ProcessSearch" data-rpa-process-search-config={JSON.stringify(config)}></div>
