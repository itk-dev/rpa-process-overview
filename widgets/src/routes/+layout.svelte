<script lang="ts">
	import favicon from '$lib/assets/favicon.svg';

	let { children } = $props();
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
	<link
		rel="stylesheet"
		href="https://rpa-process-overview.local.itkdev.dk/assets/styles/app-static.css"
	/>
</svelte:head>

<div>
	{@render children?.()}
</div>
