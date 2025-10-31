<script lang="ts">
	import Spinner from './Icons/Spinner.svelte';
	import ExclamationMark from './Icons/ExclamationMark.svelte';
	import { type Column, type MetaFilters, type ProgressData, type RawData } from '$lib/types';
	import Table from '$lib/Table.svelte';
	import { config, t } from './config';
	import ErrorBanner from '$lib/ErrorBanner.svelte';
	import Pagination from './Pagination.svelte';
	import FilterByFailed from './FilterByFailed.svelte';

	const { page_size, data_url, title } = config;

	let error: boolean = $state(false);

	let filtersFailedAt: Column[] | null = $state(null);
	let selectedFilterFailedAt: number | null = $state(getCurrentFilterFailedAt());
	let currentMetaFilters: MetaFilters = $state(getCurrentMetaFilters());

	let data: ProgressData | null = $state(null);
	let total: number | null = $state(null);
	let fetching: boolean = $state(true);
	let errorMessage: string = $state('');
	let size: number = $state(parseInteger(page_size) || 10);
	let page: number = $state(getCurrentPage());

	function parseInteger(value: string | null): number | null {
		if (null === value) {
			return null;
		}

		const parsedValue = parseInt(value, 10);

		return isNaN(parsedValue) ? null : parsedValue;
	}

	function getCurrentPage(): number {
		const url = new URL(document.location.href);
		return parseInteger(url.searchParams.get('page')) ?? 1;
	}

	function getCurrentFilterFailedAt(): number | null {
		const url = new URL(document.location.href);
		return parseInteger(url.searchParams.get('failed_at'));
	}

	function getCurrentMetaFilters(): MetaFilters {
		const url = new URL(document.location.href);
		const filters: MetaFilters = {};
		for (const item of url.searchParams.getAll('meta_filter')) {
			const pair = item.split(':', 2);
			if (2 === pair.length) {
				filters[pair[0]] = pair[1];
			}
		}

		return filters;
	}

	function hasMetaFilter(name: string, value: string): boolean {
		return currentMetaFilters[name] === value;
	}

	function toggleMetaFilter(name: string, value: string) {
		if (hasMetaFilter(name, value)) {
			delete currentMetaFilters[name];
		} else {
			currentMetaFilters[name] = value;
		}
	}

	function setUrlSearchParams(url: URL, values: Object, useBracketsForArray: boolean = false) {
		const setValue = (name: string, value: any) => {
			// Remove name if value is empty
			if (null === value) {
				url.searchParams.delete(name);
			} else if (Array.isArray(value)) {
				url.searchParams.delete(name);
				if (useBracketsForArray) {
					name += '[]';
				}
				for (const val of value) {
					{
						url.searchParams.append(name, String(val));
					}
				}
			} else {
				url.searchParams.set(name, String(value));
			}
		};

		for (const [name, value] of Object.entries(values)) {
			setValue(name, value);
		}
	}

	function setStateUrlSearchParams(url: URL, useBracketsForArray: boolean = false) {
		const metaFilter = [];
		for (const [name, value] of Object.entries(currentMetaFilters)) {
			metaFilter.push(name + ':' + value);
		}

		setUrlSearchParams(
			url,
			{
				page,
				failed_at: selectedFilterFailedAt,
				meta_filter: metaFilter
			},
			useBracketsForArray
		);
	}

	function updateUrl() {
		const pageUrl = new URL(document.location.href);
		setStateUrlSearchParams(pageUrl);
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

		const url = new URL(data_url, document.location.href);
		setStateUrlSearchParams(url, true);
		setUrlSearchParams(url, { size }, true);

		fetch(url.toString())
			.then((response) => response.json())
			.then(({ data: receivedData, meta }: { data: ProgressData | null; meta: RawData }) => {
				if (receivedData) {
					total = meta?.total ?? null;
					data = receivedData;
					filtersFailedAt = receivedData.columns.filter(({ type }) => 'step' === type);
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

	function selectFilterFailedAt(event: Event): void {
		// Page is reset on filters, so when filtered we start from scratch, and not accidentally end up on a page that has no content
		page = 1;
		selectedFilterFailedAt = parseInteger((event.target as HTMLInputElement).value);
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
				<h2>{title}</h2>
				<span
					class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full self-center bg-rose-700 text-white"
					>{total ?? '?'}</span
				>
			</div>
			<FilterByFailed
				selectedFilter={selectedFilterFailedAt}
				selectFilter={selectFilterFailedAt}
				filters={filtersFailedAt}
			/>
		</div>
		<div class="p-4 min-h-[450px] flex flex-col justify-between">
			<Table columns={data.columns} rows={data.rows} {hasMetaFilter} {toggleMetaFilter}></Table>
			{#if total !== null}
				<Pagination {total} {changePage} {size} {page} />
			{/if}
		</div>
	</div>
{/if}
