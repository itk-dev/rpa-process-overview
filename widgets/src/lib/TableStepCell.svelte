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
	function getStatusClasses(status: String) {
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
	class={[
		'step',
		'relative',
		{
			pending: StepStatus.PENDING === cell.status,
			failed: StepStatus.FAILED === cell.status,
			succeeded: StepStatus.SUCCESS === cell.status,
			first: isFirstStep(cell.step_index),
			last: isLastStep(i, row.length)
		}
	]}
>
	<button
		class={[
			// 'flex',
			// 'items-center',
			// 'justify-center',
			// '[width:stretch]',
			{
				'cursor-pointer': cell.status !== StepStatus.PENDING
			}
		]}
		popovertarget={`popover-${cell.id}`}
		style:anchor-name={`--anchor-${cell.id}`}
	>
		<div
			class="{getStatusClasses(
				cell.status
			)} transition-colors z-1 motion-reduce:transition-none w-8 h-8 rounded-full flex items-center justify-center mx-auto text-white"
		>
			{#if posted}
				<Rerun />
			{:else}
				<!-- The icon defined where this component is initiated -->
				{@render children()}
			{/if}
		</div>
	</button>
	{#if cell.status !== StepStatus.PENDING}
		<PopOver step={cell} {posted} {updatePosted} />
	{/if}
</td>
