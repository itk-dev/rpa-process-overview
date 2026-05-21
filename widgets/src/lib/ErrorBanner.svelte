<script lang="ts">
	import Bell from './Icons/Bell.svelte';
	let { errorMessage }: { errorMessage: string } = $props();
</script>

<div
	class="flex items-center p-4 mb-4 rounded-md bg-rose-100 dark:bg-gray-800 border border-rose-700 text-gray-950 dark:text-white"
	role="alert"
>
	<Bell />
	<div>
		<span class="font-medium">{errorMessage}</span>
	</div>
</div>
