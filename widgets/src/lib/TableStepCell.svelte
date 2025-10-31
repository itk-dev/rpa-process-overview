<script lang="ts">
	import { type Step } from './types';
	import { StepStatus } from './enums';
	import type { Snippet } from 'svelte';
	import PopOver from './Popover.svelte';
	import Rerun from './Icons/Rerun.svelte';
	let {
		i,
		row,
		cell,
		children
	}: {
		i: number;
		row: Step[];
		cell: Step;
		children: Snippet;
	} = $props();

	const { status, id, step_index } = cell;

	let posted: boolean = $state(false);

	function isFirstStep(stepIndex: Number | null): boolean {
		return stepIndex === 0;
	}

	function isLastStep(index: number | null, rowLength: number | null): boolean {
		if (rowLength) {
			return index === rowLength - 1;
		}
		return false;
	}

	function updatePosted(newState: boolean): void {
		posted = newState;
	}

	// tailwind soo verbose
	function getStatusClasses() {
		if (posted) return 'bg-violet-600';

		switch (status) {
			case StepStatus.FAILED:
				return 'bg-rose-700';
			case StepStatus.SUCCESS:
				return 'bg-green-700';
			case StepStatus.PENDING:
				return 'bg-neutral-400 dark:bg-neutral-800';
		}
	}
</script>

<td
	class="relative [width:stretch] step {StepStatus.FAILED === status
		? 'failed'
		: ''} {StepStatus.SUCCESS === status ? 'succeeded' : ''} {isFirstStep(step_index)
		? 'first'
		: ''} {isLastStep(i, row.length) ? 'last' : ''}"
>
	<button
		id={`anchor-${id}`}
		class="flex items-center justify-center [width:stretch] {status !== StepStatus.PENDING
			? 'cursor-pointer'
			: ''}"
		popovertarget={`popover-${id}`}
	>
		<div
			class="{getStatusClasses()} transition-colors z-1 motion-reduce:transition-none w-8 h-8 rounded-full flex items-center justify-center mx-auto text-white"
		>
			{#if posted}
				<Rerun />
			{:else}
				<!-- The icon defined where this component is initiated -->
				{@render children()}
			{/if}
		</div>
	</button>
	{#if status !== StepStatus.PENDING}
		<PopOver step={cell} {posted} {updatePosted} />
	{/if}
</td>
