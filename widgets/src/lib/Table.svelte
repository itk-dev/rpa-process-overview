<script lang="ts">
	import CheckMark from './Icons/CheckMark.svelte';
	import Clock from './Icons/Clock.svelte';
	import Close from './Icons/Close.svelte';
	import { type Step, type Column } from './types';
	import TableStepCell from './TableStepCell.svelte';
	import TableMetaCell from './TableMetaCell.svelte';
	import { StepStatus } from './enums';
	import MetaFilterWidget from "$lib/MetaFilterWidget.svelte";

	let { columns, rows }: { columns: Column[] | null; rows: Array<Array<Step>> | null } = $props();

	// Status icons
	// Todo, perhaps a "Running" status will be added
	const icons = {
		[StepStatus.PENDING]: Clock,
		[StepStatus.SUCCESS]: CheckMark,
		[StepStatus.FAILED]: Close
	};
</script>

<table class="min-w-full">
	{#if columns}
		<thead
			class="bg-gray-200 dark:bg-gray-800 border-neutral-300 dark:border-neutral-800 dark:bg-gray-800 border-b"
		>
			<tr>
				{#each columns as { label }}
					<th class="px-1 py-3">
						<span
							class="max-w-[140px] whitespace-prewrap overflow-hidden text-ellipsis text-center text-xs font-medium dark:text-gray-400"
						>
							{label}
						</span>
					</th>
				{/each}
			</tr>
		</thead>
	{/if}
	{#if columns && rows != null}
		<tbody>
			{#if rows.length > 0}
				{#each rows as row}
					<tr
						class="hover:bg-neutral-300 dark:hover:bg-gray-800 border-b border-neutral-300 dark:border-neutral-800"
					>
						{#each row as cell, i}
							{#if cell.status}
								<TableStepCell {cell} {row} {i}>
									{@const IconComponent = icons[cell.status]}
									<IconComponent />
								</TableStepCell>
							{:else}
								<TableMetaCell rawValueUrl={cell.raw_value_url}>
									<!-- Let's keep the little ghost -->
									{cell.value ?? cell.status ?? '👻'}
									<MetaFilterWidget name={columns[i].value_name} {cell}/>
								</TableMetaCell>
							{/if}
						{/each}
					</tr>
				{/each}
			{/if}
		</tbody>
	{/if}
</table>
