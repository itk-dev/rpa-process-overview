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
		data_url: 'https://rpa-process-overview.local.itkdev.dk/group/1/overview/1/data',
		page_size: 10,
	};
</script>

<div id="ProcessOverview" data-rpa-process-overview-config={JSON.stringify(config)}></div>
