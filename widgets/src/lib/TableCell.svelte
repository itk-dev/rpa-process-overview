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

	function isTheNextStepFailed(row: Step[], stepIndex: Number | null) {
		if (stepIndex === null) return false;
		const indexElement = row.findIndex(({ step_index }) => step_index === stepIndex);
		const nextElement = indexElement + 1;
		return row[nextElement]?.status !== StepStatus.SUCCESS;
	}

	function notTheLastStep(index: number, rowLength: number): boolean {
		return index < rowLength - 1;
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

<!-- relative to absolute position the stick -->
<td class="relative">
	<button
		id={`anchor-${id}`}
		class="flex items-center justify-center width-stretch [width:stretch] {status !==
		StepStatus.PENDING
			? 'cursor-pointer'
			: ''}"
		popovertarget={`popover-${id}`}
	>
		<div
			class="{getStatusClasses()} transition-colors motion-reduce:transition-none w-8 h-8 rounded-full flex items-center justify-center mx-auto text-white"
		>
			{#if posted}
				<Rerun />
			{:else}
				<!-- The icon defined where this component is initiated -->
				{@render children()}
			{/if}
		</div>
	</button>
	<!-- The last step should not display the little stick between the round things -->
	{#if notTheLastStep(i, row.length)}
		<div
			class="{isTheNextStepFailed(row, step_index)
				? 'bg-neutral-400 dark:bg-neutral-800'
				: 'bg-green-700'} absolute top-1/2 left-[calc(50%+16px)] h-0.5 w-[calc(100%)] -translate-y-1/2"
		></div>
	{/if}
	{#if status !== StepStatus.PENDING}
		<PopOver step={cell} {posted} {updatePosted} />
	{/if}
</td>
