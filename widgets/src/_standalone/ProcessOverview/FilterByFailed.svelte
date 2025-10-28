<script lang="ts">
	import { t } from './config';
	import { type Column } from '$lib/types';

	let {
		filters,
		selectFilter,
		selectedFilter
	}: {
		filters: Column[] | null;
		selectFilter: Function;
		selectedFilter: number | null;
	} = $props();
</script>

{#if filters}
	<div>
		<label for="filter-select" class="mx-2">{t('Viewing processes failed in')}</label>
		<select
			value={selectedFilter}
			onchange={(e) => selectFilter(e)}
			id="filter-select"
			class="rounded-md bg-violet-600 text-white py-1 px-2 border-6 border-violet-600"
		>
			<option value={null}>{t('Show all')}</option>
			{#each filters as { label, id }}
				<option value={id}>{label}</option>
			{/each}
		</select>
	</div>
{/if}
