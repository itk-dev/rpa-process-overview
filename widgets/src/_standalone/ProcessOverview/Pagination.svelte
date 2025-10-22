<script lang="ts">
	import LeftArrow from '../../lib/Icons/LeftArrow.svelte';
	import RightArrow from '../../lib/Icons/RightArrow.svelte';
	import { t } from './config';

	let {
		totalAmount,
		size,
		page,
		changePage,
		totalAmountOfButtons
	}: {
		size: number;
		page: number;
		totalAmount: number;
		changePage: Function;
		totalAmountOfButtons: number;
	} = $props();

	const totalAmountOfPagesAsIntegerArray = Array.from(
		{ length: totalAmountOfButtons },
		(_, i) => i + 1
	);

	function isThisTheLastPage(): boolean {
		return page === totalAmountOfButtons;
	}

	function isThisTheFirstPage(): boolean {
		return page === 1;
	}
</script>

<div
	class="flex items-center overflow-y-scroll justify-between border-t border-neutral-400 dark:border-neutral-700 px-4 py-3"
>
	<div class="min-w-[200px] flex items-center text-sm dark:text-gray-300">
		<!-- Todo find a way to handle placeholders -->
		<span>{size * page - size}-{size * page} {t('of')} {totalAmount}</span>
	</div>
	<div class="flex items-center space-x-2">
		{#if !isThisTheFirstPage()}
			<button
				aria-describedby="prev-page-label"
				onclick={() => changePage(page - 1)}
				class="cursor-pointer flex justify-center items-center h-8 w-8 rounded-md text-gray-800 dark:text-gray-300 dark:hover:bg-violet-600 hover:bg-violet-300 text-gray-100"
			>
				<LeftArrow />
				<span class="sr-only" id="prev-page-label">{t('Go to previous page')}</span>
			</button>
		{/if}
		<div class="flex items-center space-x-1">
			{#each totalAmountOfPagesAsIntegerArray as index}
				<span class="sr-only" id={`go-to-page-${index}`}>{t('Go to page')}</span>
				<button
					aria-describedby={`go-to-page-${index}`}
					onclick={() => changePage(index)}
					class="cursor-pointer flex justify-center items-center h-8 w-8 rounded-md text-gray-800 dark:text-gray-300 dark:hover:bg-violet-600 hover:bg-violet-300 text-gray-100 {page ===
					index
						? 'bg-violet-600 text-white'
						: ''}">{index}</button
				>
			{/each}
		</div>
		{#if size + page >= totalAmount}
			<!-- The below is to avoid the UI jumping around because the pagination button disappears -->
			<div class="h-8 w-8 bg-transparent"></div>
		{/if}

		{#if !isThisTheLastPage()}
			<button
				aria-describedby="next-page-label"
				onclick={() => changePage(page + 1)}
				class="cursor-pointer flex justify-center items-center h-8 w-8 rounded-md text-gray-800 dark:text-gray-300 dark:hover:bg-violet-600 hover:bg-violet-300 text-gray-100"
				><RightArrow />
				<span class="sr-only" id="next-page-label">{t('Go to next page')}</span>
			</button>
		{/if}
	</div>
</div>
