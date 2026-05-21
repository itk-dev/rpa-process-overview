<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		id,
		changePage,
		label,
		selected = false,
		disabled = false,
		children
	}: {
		id: string;
		label: string;
		changePage: Function;
		selected?: boolean;
		disabled?: boolean;
		children: Snippet;
	} = $props();
</script>

<span class="sr-only" id={`go-to-page-${id}`}>{label}</span>
<button
	aria-describedby={`go-to-page-${id}`}
	onclick={() => changePage()}
	{disabled}
	class="cursor-pointer flex disabled:text-gray-300 dark:disabled:text-gray-700 hover:disabled:bg-transparent justify-center items-center h-8 w-8 rounded-md text-gray-800 dark:text-gray-300 dark:hover:bg-violet-600 hover:bg-violet-300 text-gray-100 {selected
		? 'bg-violet-600 text-white'
		: ''}"
>
	{@render children()}
</button>
