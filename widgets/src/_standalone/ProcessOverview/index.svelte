<script lang="ts">
	import Spinner from './Icons/Spinner.svelte';
	import ExclamationMark from './Icons/exclamationMark.svelte';
	import { type ProgressData } from './types';
	import Table from './Components/Table.svelte';

	let error: Boolean = $state(false);
	let data: ProgressData | null = $state(null);
	let total: Number | null = $state(null);
	let fetching = $state(true);
	const config = (() => {
		try {
			return JSON.parse(document.getElementById('ProcessOverview')?.dataset.config || '{}');
		} catch (error) {
			return {};
		}
	})();

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
			<h2 class="my-3 text-white">Loading data...</h2>
		</Spinner>
	</div>
{:else if null === data}
	<div class="my-3 text-white"><h2 class="p-4">Missing data</h2></div>
{:else}
	<div class="flex flex-col border bg-gray-900 border-neutral-800 rounded-md shadow-sm">
		<div
			class=" p-3 text-white font-medium bg-gray-800 flex items-center border-b border-neutral-800"
		>
			<ExclamationMark />
			<span>Fejlede processer</span>
			<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-700 text-white"
				>{total ?? '?'}</span
			>
		</div>
		<div class="p-4">
			<Table columns={data.columns} rows={data.rows}></Table>
		</div>
	</div>
{/if}
