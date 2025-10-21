<script lang="ts">
	import Spinner from './Icons/Spinner.svelte';
	import ExclamationMark from './Icons/ExclamationMark.svelte';
	import { type ProgressData } from '$lib/types';
	import Table from '$lib/Table.svelte';
	import { t, config } from './config';
	import ErrorBanner from '$lib/ErrorBanner.svelte';
	import Pagination from './Pagination.svelte';

	const { page_size, data_url } = config;

	let error: boolean = $state(false);
	let data: ProgressData | null = $state(null);
	let total: number | null = $state(null);
	let fetching: boolean = $state(true);
	let errorMessage: string = $state('');
	let size: number = $state(parseInt(page_size));
	let page: number = $state(getCurrentPage());

	function getCurrentPage(): number {
		const url = new URL(document.location.href);
		const page = Number(url.searchParams.get('page')) || null;
		return page ?? 1;
	}

	function updateUrl(): void {
		const pageUrl = new URL(document.location.href);
		pageUrl.searchParams.set('page', String(page));
		history.replaceState({}, '', pageUrl);
	}

	function changePage(index: number): void {
		page = index;
	}

	$effect(() => {
		updateUrl();
		fetching = true;
		errorMessage = '';
		error = false;

		fetch(`${data_url}?page=${page}&size=${size}`)
			.then((response) => response.json())
			.then(({ data: recievedData, meta }) => {
				if (recievedData) {
					total = meta?.total ?? null;
					data = recievedData;
				}
				fetching = false;
			})
			.catch((e) => {
				console.error(e);
				fetching = false;
				error = true;
				errorMessage = t('An error occurred while fetching the data');
			});
	});
</script>

{#if error}
	<ErrorBanner {errorMessage} />
{:else if fetching}
	<div class="flex justify-center mb-5">
		<Spinner>
			<h2 class="my-3 dark:text-white text-neutral-900">{t('Loading data...')}</h2>
		</Spinner>
	</div>
{:else if null === data}
	<h2 class="text-neutral-900 dark:text-white my-3 mb-5">{t('Missing data')}</h2>
{:else}
	<div
		class="flex flex-col overflow-scroll border bg-gray-100 dark:bg-gray-900 border-neutral-300 dark:border-neutral-800 rounded-md mb-5"
	>
		<div
			class="p-3 dark:text-white font-medium bg-gray-200 dark:bg-gray-800 flex items-center border-b border-neutral-300 dark:border-neutral-800"
		>
			<ExclamationMark />
			<h2>{t('Failed processes')}</h2>
			<span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-700 text-white"
				>{total ?? '?'}</span
			>
		</div>
		<div class="p-4 min-h-[450px] flex flex-col justify-between">
			<Table columns={data.columns} rows={data.rows}></Table>
			{#if total !== null}
				<Pagination
					totalAmountOfButtons={Math.ceil(total / size)}
					totalAmount={total}
					{changePage}
					{size}
					{page}
				/>
			{/if}
		</div>
	</div>
{/if}
