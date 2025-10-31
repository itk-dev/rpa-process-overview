<script lang="ts">
	import favicon from '$lib/assets/favicon.svg';

	let { children } = $props();
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
	<link rel="stylesheet" href="http://127.0.0.1:8787/assets/styles/app-static.css" />
</svelte:head>

<div>
	{@render children?.()}
</div>
