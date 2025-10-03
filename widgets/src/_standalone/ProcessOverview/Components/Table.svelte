<script lang="ts">
	import CheckMark from '../Icons/CheckMark.svelte';
	import Clock from '../Icons/Clock.svelte';
	import Close from '../Icons/Close.svelte';
	import { type Step, type Column } from '../types';
	import TableCell from './TableCell.svelte';
	import Status from '../enums';

	let { columns, rows }: { columns: Column[] | null; rows: Array<Array<Step>> | null } = $props();

	// Status icons
	// Todo, perhaps a "Running" status will be added
	const icons = {
		[Status.PENDING]: Clock,
		[Status.SUCCESS]: CheckMark,
		[Status.FAILED]: Close
	};
</script>

<table class="min-w-full">
	{#if columns}
		<thead class="bg-gray-800 border-b border-neutral-800">
			<tr>
				{#each columns as { label }}
					<th class="px-2 py-3 text-center text-xs font-medium text-gray-400 w-24">
						<span class="block text-center whitespace-normal">{label}</span>
					</th>
				{/each}
			</tr>
		</thead>
	{/if}
	{#if columns && rows != null}
		<tbody>
			{#if rows.length > 0}
				{#each rows as row}
					<tr class="hover:bg-gray-800 border-b border-neutral-800">
						{#each row as cell, i}
							{#if cell.status}
								<TableCell
									{cell}
									{row}
									{i}
									className={cell.status === Status.SUCCESS
										? 'bg-green-700'
										: cell.status === Status.FAILED
											? 'bg-rose-700'
											: 'bg-neutral-800'}
								>
									{@const IconComponent = icons[cell.status]}
									<IconComponent />
								</TableCell>
							{:else}
								<td class="px-3 py-4 whitespace-nowrap text-sm text-gray-300 w-24 truncate">
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
