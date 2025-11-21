<script lang="ts">
	import { onDestroy } from 'svelte';
	import Table from '$lib/Table.svelte';
	import Search from './Icons/Search.svelte';
	import { t, config } from './config';
	import type { ProgressData } from '$lib/types';
	import ErrorBanner from '$lib/ErrorBanner.svelte';

	let query: string = $state('');
	let errorMessage: string = $state('');
	let error: boolean = $state(false);
	const DEBOUNCE_DELAY: number = 500;
	let data: ProgressData | null = $state(null);
	let timer: ReturnType<typeof setTimeout>;
	const { search_url, minimum_search_query_length, title } = config;

	$effect(() => {
		const parsedQuery = query.trim();
		clearTimeout(timer);
		errorMessage = '';
		error = false;

		// Debounce setTimeout
		timer = setTimeout(() => {
			if (parsedQuery && parsedQuery.length >= minimum_search_query_length) {
				const url = new URL(search_url, document.location.href);
				url.searchParams.set('q', parsedQuery);
				fetch(url.toString())
					.then((response) => response.json())
					.then(({ data: recievedData }) => {
						if (recievedData) {
							data = recievedData;
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
	<div class="rounded-md h-full border border-neutral-300 dark:border-neutral-800">
		<div
			class="p-3 dark:text-white font-medium bg-gray-200 dark:bg-gray-800 flex justify-between items-center border-b border-neutral-300 dark:border-neutral-800"
		>
			<div class="flex items-center">
				<Search />
				<h2>{title}</h2>
			</div>
		</div>

		<div class="p-4 bg-gray-100 dark:bg-gray-900">
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
			</div>
		</div>

		{#if data}
			<Table columns={data.columns} rows={data.rows}></Table>
		{/if}
	</div>
</div>
