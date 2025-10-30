<script lang="ts">
	import Spinner from './Icons/Spinner.svelte';
	import ExclamationMark from './Icons/ExclamationMark.svelte';
	import { type ProgressData, type Column, type RawData } from '$lib/types';
	import Table from '$lib/Table.svelte';
	import { t, config } from './config';
	import ErrorBanner from '$lib/ErrorBanner.svelte';
	import Pagination from './Pagination.svelte';
	import FilterByFailed from './FilterByFailed.svelte';

	const { page_size: pageSize, data_url: dataUrl, page_headline: pageHeadline } = config;


	let error: boolean = $state(false);
	let filters: Column[] | null = $state(null);
	let selectedFilter: number | null = $state(getCurrentFilter());
	let data: ProgressData | null = $state(null);
	let total: number | null = $state(null);
	let fetching: boolean = $state(true);
	let errorMessage: string = $state('');
	let size: number = $state(parseInt(pageSize));
	let page: number = $state(getCurrentPage());

	function parseInteger(int: string | null): number | null {
		if (int) {
			return Number(int);
		}
		return null;
	}

	function getCurrentPage(): number {
		const url = new URL(document.location.href);
		const page = parseInteger(url.searchParams.get('page'));
		return page ?? 1;
	}
	function getCurrentFilter(): number | null {
		const url = new URL(document.location.href);
		const failedAt = parseInteger(url.searchParams.get('failed_at'));
		return failedAt;
	}

	function updateUrl(): void {
		const pageUrl = new URL(document.location.href);
		pageUrl.searchParams.set('page', String(page));
		if (selectedFilter) {
			pageUrl.searchParams.set('failed_at', String(selectedFilter));
		}
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

		const url = new URL(dataUrl, document.location.href);
		url.searchParams.set('page', String(page));

		if (selectedFilter) {
			url.searchParams.set('failed_at', String(selectedFilter));
		}

		url.searchParams.set('size', String(size));

		fetch(url.toString())
			.then((response) => response.json())
			.then(({ data: recievedData, meta }: { data: ProgressData | null; meta: RawData }) => {
				if (recievedData) {
					total = meta?.total ?? null;
					data = recievedData;
					filters = recievedData.columns.filter(({ type }) => 'step' === type);
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

	function selectFilter(event: Event): void {
		// Page is reset on filters, so when filtered we start from scratch, and not accidentally end up on a page that has no content
		page = 1;
		selectedFilter = parseInteger((event.target as HTMLInputElement).value);
	}
</script>

{#if error}
	<ErrorBanner {errorMessage} />
{:else if fetching}
	<div class="flex justify-center mb-5">
		<Spinner>
			<h2 class="my-3 dark:text-white text-neutral-900">{t('Loading data …')}</h2>
		</Spinner>
	</div>
{:else if null === data}
	<h2 class="text-neutral-900 dark:text-white my-3 mb-5">{t('Missing data')}</h2>
{:else}
	<div
		class="flex flex-col border bg-gray-100 dark:bg-gray-900 border-neutral-300 dark:border-neutral-800 rounded-md mb-5"
	>
		<div
			class="p-4 dark:text-white justify-between font-medium bg-gray-200 dark:bg-gray-800 flex items-center border-b border-neutral-300 dark:border-neutral-800"
		>
			<div class="flex">
				<ExclamationMark />
				<h2>{pageHeadline}</h2>
				<span
					class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full self-center bg-rose-700 text-white"
					>{total ?? '?'}</span
				>
			</div>
			<FilterByFailed {selectedFilter} {selectFilter} {filters} />
		</div>
		<div class="p-4 min-h-[450px] flex flex-col justify-between">
			<Table columns={data.columns} rows={data.rows}></Table>
			{#if total !== null}
				<Pagination {total} {changePage} {size} {page} />
			{/if}
		</div>
	</div>
{/if}
