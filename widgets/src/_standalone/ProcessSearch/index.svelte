<script lang="ts">
	import { onDestroy } from 'svelte';
	import Table from '$lib/Table.svelte';
	import Search from './Icons/Search.svelte';
	import { t, config } from './config';
	import type { ProgressData } from '$lib/types';
	import Person from './Icons/Person.svelte';
	import ErrorBanner from '$lib/ErrorBanner.svelte';

	let query: string = $state('');
	let errorMessage: string = $state('');
	let error: boolean = $state(false);
	const DEBOUNCE_DELAY: number = 500;
	let name: string = $state('');
	let data: ProgressData | null = $state(null);
	const { search_url, minimum_search_query_length } = config;
	let timer: ReturnType<typeof setTimeout>;

	$effect(() => {
		const parsedQuery = query.trim();
		clearTimeout(timer);
		errorMessage = '';
		error = false;

		// Debounce setTimeout
		timer = setTimeout(() => {
			if (parsedQuery && parsedQuery.length >= minimum_search_query_length) {
				const url = new URL(search_url, document.location.href);
				fetch(url.toString())
					.then((response) => response.json())
					.then(({ data: recievedData }) => {
						if (recievedData) {
							data = recievedData;
							name = recievedData.data?.items[0]?.meta?.name;
						}
					})
					.catch((e) => {
						console.error(e);
						error = true;
						errorMessage = t('An error occurred while searching');
					});
			}
		}, DEBOUNCE_DELAY);
	});

	// I am actually not sure this component is ever destroyed
	onDestroy(() => {
		clearInterval(timer);
	});
</script>

<div class="grid grid-cols-1 gap-6">
	<div class="rounded-md overflow-hidden h-full border border-neutral-300 dark:border-neutral-800">
		<div
			class="p-3 dark:text-white font-medium bg-gray-200 dark:bg-gray-800 flex items-center border-b border-neutral-300 dark:border-neutral-800"
		>
			<Search />
			<h2>{t('Citizen search')}</h2>
		</div>

		<div class="p-4 flex flex-row gap-4 bg-gray-100 dark:bg-gray-900">
			<div class="w-1/3 relative">
				<div class="absolute left-3 top-5 transform -translate-y-1/2">
					<Search className="h-5 w-5 mr-2 text-neutral-400" />
				</div>
				<input
					id="search"
					type="search"
					class="dark:bg-gray-950 bg-white border border-neutral-300 dark:border-neutral-800 outline-none rounded-md pl-10 pr-4 py-2 w-full transition-all motion-reduce:transition-none dark:text-white text-gray-900 focus:border-neutral-400 focus:border-1 mb-2"
					bind:value={query}
				/>
				{#if error}
					<ErrorBanner {errorMessage} />
				{/if}

				{#if data}
					<div
						class="dark:bg-black bg-white rounded-md border border-neutral-300 dark:border-neutral-800 p-4"
					>
						<h3 class="dark:text-white text-gray-950 text-sm font-medium mb-2">
							{t('Citizen information')}
						</h3>
						<div class="space-y-3">
							<div class="flex items-center">
								<Person className="dark:text-white text-gray-750 h-4 w-4 mr-2" />
								<div>
									<div class="text-xs dark:text-white text-gray-750 text-bold">{t('Name')}</div>
									<div class="text-sm dark:text-white text-gray-950">{name}</div>
								</div>
							</div>
						</div>
					</div>
				{/if}
			</div>

			{#if data}
				<div class="w-2/3 overflow-scroll">
					<Table columns={data.columns} rows={data.rows}></Table>
				</div>
			{/if}
		</div>
	</div>
</div>
