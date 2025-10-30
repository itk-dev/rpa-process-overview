<script lang="ts">
	import { t } from '../_standalone/ProcessOverview/config';

	let {
		column,
		cell,
		getToggleMetaFilterUrl,
		getCurrentMetaFilter,
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
	const hasFilter = value === getCurrentMetaFilter(name);
</script>

{#if is_filterable}
	<pre>{JSON.stringify({name})}</pre>
	[<button on:click={() => toggleMetaFilter(name, value)}>
		{hasMetaFilter(name)
			? t('Remove filter on "{value}"', { value })
			: t('Show only "{value}"', { value })}
	</button>] [<a data-sveltekit-reload href={getToggleMetaFilterUrl(name, value)}
		>{hasFilter
			? t('Remove filter on "{value}"', { value })
			: t('Show only "{value}"', { value })}</a
	>]
{/if}
