<script lang="ts">
	import { type Step } from '../types';
	import Status from '../enums';
	import type { Snippet } from 'svelte';

	let {
		i,
		row,
		cell,
		className,
		children
	}: { i: number; row: Step[]; cell: Step; className: string; children: Snippet } = $props();

	function isTheNextStepFailed(row: Step[], stepIndex: Number | null) {
		if (stepIndex === null) return false;
		const indexElement = row.findIndex(({ step_index }) => step_index === stepIndex);
		const nextElement = indexElement + 1;
		return row[nextElement]?.status !== Status.SUCCESS;
	}

	function notTheLastStep(index: number, rowLength: number): boolean {
		return index < rowLength - 1;
	}
</script>

<!-- relative to absolute position the stick -->
<td class="relative">
	<div
		class="{className} w-8 h-8 rounded-full flex items-center justify-center z-10 relative mx-auto text-white"
	>
		<!-- The icon defined where this component is initiated -->
		{@render children()}
	</div>
	<!-- The last step should not display the little stick between the round things -->
	{#if notTheLastStep(i, row.length)}
		<div
			class="{isTheNextStepFailed(row, cell.step_index)
				? 'bg-neutral-800'
				: 'bg-green-700'} absolute top-1/2 left-[calc(50%+16px)] h-0.5 w-[calc(100%-32px)] -translate-y-1/2 z-0"
		></div>
	{/if}
</td>
