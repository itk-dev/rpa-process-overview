<script lang="ts">
	import { t } from '../_standalone/ProcessOverview/config';
	import MetaFilterRemove from '$lib/Icons/MetaFilterRemove.svelte';
	import MetaFilterAdd from '$lib/Icons/MetaFilterAdd.svelte';

	let {
		column,
		cell,
		hasMetaFilter,
		toggleMetaFilter
	}: {
		column: object;
		cell: object;
		getToggleMetaFilterUrl: Function;
		getCurrentMetaFilter: Function;
		hasMetaFilter: Function;
		toggleMetaFilter: Function;
	} = $props();

	const { value_name: name, is_filterable } = column;
	const { value } = cell;
</script>

{#if is_filterable}
	<button class="cursor-pointer" onclick={() => toggleMetaFilter(name, value)}>
		{#if hasMetaFilter(name, value)}
			<span title={t('Remove filter on "{value}"', { value })}><MetaFilterRemove /></span>
		{:else}
			<span title={t('Show only "{value}"', { value })}><MetaFilterAdd /></span>
		{/if}
	</button>
{/if}
