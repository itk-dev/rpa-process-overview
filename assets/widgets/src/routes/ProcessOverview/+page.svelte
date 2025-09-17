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
        data_url: "/api/v1/data.json"
    };
</script>

<div id="ProcessOverview" data-config={JSON.stringify(config)}></div>

<style>
@import "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css";
</style>
