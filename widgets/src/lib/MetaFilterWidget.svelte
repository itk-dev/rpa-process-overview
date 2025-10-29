<script lang="ts">
	import { type Step } from './types';
	import { StepStatus } from './enums';
	import type { Snippet } from 'svelte';
	import PopOver from './Popover.svelte';
	import Rerun from './Icons/Rerun.svelte';
	let {
		name,
		cell,
	}: {
		name: string,
		cell: object,
	} = $props();

	const { value } = cell;
</script>

<pre>{JSON.stringify({name, value})}</pre>
