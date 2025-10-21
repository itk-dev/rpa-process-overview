<script lang="ts">
	import CheckMark from './Icons/CheckMark.svelte';
	import Clock from './Icons/Clock.svelte';
	import Close from './Icons/Close.svelte';
	import { type Step, type Column } from './types';
	import TableCell from './TableCell.svelte';
	import { StepStatus } from './enums';

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
						<button
							class="max-w-[140px] whitespace-prewrap overflow-hidden text-ellipsis text-center text-xs font-medium dark:text-gray-400"
							aria-label="todo"
						>
							{label}
						</button>
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
								<TableCell
									{cell}
									{row}
									{i}
									className={cell.status === StepStatus.SUCCESS
										? 'bg-green-700'
										: cell.status === StepStatus.FAILED
											? 'bg-rose-700'
											: 'bg-neutral-400 dark:bg-neutral-800'}
								>
									{@const IconComponent = icons[cell.status]}
									<IconComponent />
								</TableCell>
							{:else}
								<td class="px-3 py-4 whitespace-nowrap text-sm dark:text-gray-300 w-24 truncate">
									<!-- Let's keep the little ghost -->
									{cell.value ?? cell.status ?? '👻'}
								</td>
							{/if}
						{/each}
					</tr>
				{/each}
			{/if}
		</tbody>
	{/if}
</table>
