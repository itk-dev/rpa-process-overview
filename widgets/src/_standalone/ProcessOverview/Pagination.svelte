<script lang="ts">
	import LeftArrow from '../../lib/Icons/LeftArrow.svelte';
	import RightArrow from '../../lib/Icons/RightArrow.svelte';
	import { t } from './config';
	import PaginationButton from './PaginationButton.svelte';

	let {
		total,
		size,
		page,
		changePage
	}: {
		size: number;
		page: number;
		total: number;
		changePage: Function;
	} = $props();
	const totalAmountOfButtons: number = total / size;
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
	class="flex items-center justify-between border-t border-neutral-400 dark:border-neutral-700 px-4 py-3"
>
	<div class="min-w-[200px] flex items-center text-sm dark:text-gray-300">
		<!-- Todo find a way to handle placeholders -->
		<span>{size * page - size}-{size * page} {t('of')} {total}</span>
	</div>
	<div class="flex items-center space-x-2">
		<PaginationButton
			disabled={isThisTheFirstPage()}
			label={t('Go to previous page')}
			id={'prev'}
			changePage={() => changePage(page - 1)}
		>
			<LeftArrow />
		</PaginationButton>
		<div class="flex items-center space-x-1">
			{#each totalAmountOfPagesAsIntegerArray as index}
				<PaginationButton
					selected={page === index}
					label={t('Go to page')}
					id={String(index)}
					changePage={() => changePage(index)}>{index}</PaginationButton
				>
			{/each}
		</div>

		<PaginationButton
			disabled={isThisTheLastPage()}
			label={t('Go to next page')}
			id="next"
			changePage={() => changePage(page + 1)}
		>
			<RightArrow />
		</PaginationButton>
	</div>
</div>
