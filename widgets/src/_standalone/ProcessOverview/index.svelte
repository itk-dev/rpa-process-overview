<script lang="ts">
	import Spinner from './Icons/Spinner.svelte';
	import ExclamationMark from './Icons/ExclamationMark.svelte';
	import Table from './Components/Table.svelte';
	import { type ProgressData } from './types';
	import { t, config } from './config';

	// Todo use this error thingy
	let error: Boolean = $state(false);
	let data: ProgressData | null = $state(null);
	let total: Number | null = $state(null);
	let fetching = $state(true);

	$effect(() => {
		fetching = true;
		const url = new URL(config.data_url, document.location.href);
		fetch(url.toString())
			.then((response) => response.json())
			.then(({ data: recievedData, meta }) => {
				if (recievedData) {
					total = meta?.total ?? null;
					data = recievedData;
				}
				fetching = false;
			})
			.catch(() => {
				error = true;
			});
	});
</script>

{#if fetching}
	<div class="flex justify-center">
		<Spinner>
			<h2 class="my-3 text-white">{t('Loading data...')}</h2>
		</Spinner>
	</div>
{:else if null === data}
	<div class="my-3 text-white"><h2 class="p-4">{t('Missing data')}</h2></div>
{:else}
	<div class="flex flex-col border bg-gray-900 border-neutral-800 rounded-md shadow-sm">
		<div
			class=" p-3 text-white font-medium bg-gray-800 flex items-center border-b border-neutral-800"
		>
			<ExclamationMark />
			<h2>{t('Failed processes')}</h2>
			<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-700 text-white"
				>{total ?? '?'}</span
			>
		</div>
		<div class="p-4">
			<Table columns={data.columns} rows={data.rows}></Table>
		</div>
	</div>
{/if}
