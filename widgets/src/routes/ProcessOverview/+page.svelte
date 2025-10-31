<script lang="ts">
	import { onMount } from 'svelte';

	onMount(() => {
		const script = document.createElement('script');
		script.src = '/src/_standalone/ProcessOverview/embed.ts';
		script.async = true;
		script.type = 'module';

		document.head.appendChild(script);

		return () => {
			document.head.removeChild(script);
			window.ProcessOverview.stop();
		};
	});

	const config = {
		data_url: 'http://127.0.0.1:8787/group/1/overview/1/data',
		page_size: 10
	};
</script>

<div id="ProcessOverview" data-rpa-process-overview-config={JSON.stringify(config)}></div>
