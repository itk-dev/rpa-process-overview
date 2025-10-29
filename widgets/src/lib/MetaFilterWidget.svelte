<script lang="ts">
	import { t } from '../_standalone/ProcessOverview/config';

	let {
		column,
		cell,
		getToggleMetaFilterUrl,
		getCurrentMetaFilter
	}: {
		column: object;
		cell: object;
		getToggleMetaFilterUrl: Function;
		getCurrentMetaFilter: Function;
	} = $props();

	const { value_name: name, is_filterable } = column;
	const { value } = cell;
	const hasFilter = value === getCurrentMetaFilter(name);
</script>

{#if is_filterable}
	[<a data-sveltekit-reload href={getToggleMetaFilterUrl(name, value)}
		>{hasFilter
			? t('Remove filter on "{value}"', { value })
			: t('Show only "{value}"', { value })}</a
	>]
{/if}
