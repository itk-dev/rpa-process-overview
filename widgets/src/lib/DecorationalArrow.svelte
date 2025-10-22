<script lang="ts">
	import type { Status } from './types';
	import { StepStatus } from './enums';

	let {
		status
	}: {
		status: Status;
	} = $props();
</script>

<div
	class="w-0 h-0 border-x-15 border-x-transparent border-b-15
			{status === StepStatus.SUCCESS
		? 'border-b-green-700'
		: status === StepStatus.FAILED
			? 'border-b-rose-700'
			: 'border-b-neutral-400'}"
></div>
