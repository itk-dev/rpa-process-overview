<script lang="ts">
	import CheckMark from './Icons/CheckMark.svelte';
	import Clock from './Icons/Clock.svelte';
	import Close from './Icons/Close.svelte';
	import { type Step, type Column } from './types';
	import TableStepCell from './TableStepCell.svelte';
	import TableMetaCell from './TableMetaCell.svelte';
	import { StepStatus } from './enums';
	import MetaFilterWidget from '$lib/MetaFilterWidget.svelte';

	let {
		columns,
		rows,
		hasMetaFilter,
		toggleMetaFilter
	}: {
		columns: Column[] | null;
		rows: Array<Array<Step>> | null;
		hasMetaFilter?: Function | null;
		toggleMetaFilter?: Function | null;
	} = $props();
</script>

<table class="min-w-full">
	{#if columns}
		<thead
			class="bg-gray-200 dark:bg-gray-800 border-neutral-300 dark:border-neutral-800 dark:bg-gray-800 border-b"
		>
			<tr>
				{#each columns as { type, label }}
					<th class="{type} px-3 py-3">
						<p
							class="whitespace-pre overflow-hidden text-ellipsis text-xs font-medium dark:text-gray-400"
						>
							{label}
						</p>
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
							{#if 'step' === cell.type}
								<TableStepCell {cell} {row} {i}>
									{#if cell.status === StepStatus.PENDING}
										<Clock />
									{:else if cell.status === StepStatus.SUCCESS}
										<CheckMark />
									{:else if cell.status === StepStatus.FAILED}
										<Close />
									{/if}
								</TableStepCell>
							{:else}
								<TableMetaCell rawValueUrl={cell.raw_value_url}>
									<!-- Let's keep the little ghost -->
									{cell.value ?? cell.status ?? '👻'}
									{#if hasMetaFilter && toggleMetaFilter}
										<MetaFilterWidget
											column={columns[i]}
											{cell}
											{hasMetaFilter}
											{toggleMetaFilter}
										/>
									{/if}
								</TableMetaCell>
							{/if}
						{/each}
					</tr>
				{/each}
			{/if}
		</tbody>
	{/if}
</table>
