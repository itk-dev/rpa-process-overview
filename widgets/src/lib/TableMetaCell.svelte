<script lang="ts">
	import { onDestroy } from 'svelte';
	import Scissor from './Icons/Scissor.svelte';
	import { t } from '../_standalone/ProcessOverview/config';
	import type { Snippet } from 'svelte';

	let error: boolean = $state(false);
	let feedback: string = $state('');
	let feedbackClass: string = $state('');
	let timer: ReturnType<typeof setTimeout>;

	const FEEDBACK_VISIBLE: number = 3000;

	let {
		rawValueUrl,
		children
	}: {
		rawValueUrl: string | null;
		children: Snippet;
	} = $props();

	function showFeedback(currentFeedback: string): void {
		feedback = currentFeedback;
		feedbackClass = error ? 'bg-red-100' : 'bg-violet-100';
		timer = setTimeout(() => {
			feedbackClass = '';
			feedback = '';
			error = false;
		}, FEEDBACK_VISIBLE);
	}

	function getUnmaskedValue(url: string) {
		clearTimeout(timer);
		fetch(url)
			.then((response) => response.json())
			.then(({ value }) => {
				try {
					navigator.clipboard.writeText(value);
					showFeedback(t('Copied!'));
				} catch {
					error = true;
					showFeedback(t('Try again!'));
				}
			})
			.catch((e) => {
				console.error(e);
				error = true;
				showFeedback(t('Try again!'));
			});
	}

	onDestroy(() => {
		clearInterval(timer);
	});
</script>

<td
	class="meta relative px-3 py-4 whitespace-nowrap text-sm dark:text-gray-300 w-24 truncate h-[80px] transition {feedbackClass}"
>
	{#if rawValueUrl}
		<button class="cursor-pointer items-center flex" onclick={() => getUnmaskedValue(rawValueUrl)}>
			<div class="rounded-full bg-violet-600 p-1 mr-1 dark:hover:bg-violet-600 hover:bg-violet-300">
				<Scissor className="h-4 w-4 text-white" />
			</div>
			{@render children()}
		</button>
	{:else}
		<div>
			{@render children()}
		</div>
	{/if}
	<div
		class="absolute bottom-2 left-1/2 transform -translate-x-1/2 font-bold {error
			? 'text-red-600'
			: 'text-violet-600'} transition-all motion-reduce:transition-none text-center"
	>
		{feedback}
		<span class="sr-only">{t('Copy field')}</span>
	</div>
</td>
