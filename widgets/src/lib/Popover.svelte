<script lang="ts">
	import type { Step } from './types';
	import { StepStatus } from './enums';
	import dayjs from 'dayjs';
	import localeDa from 'dayjs/locale/da';
	import localizedFormat from 'dayjs/plugin/localizedFormat';
	import { t } from '../_standalone/ProcessOverview/config';
	import Spinner from '../_standalone/ProcessOverview/Icons/Spinner.svelte';
	import Bell from './Icons/Bell.svelte';
	import DecorationalArrow from './DecorationalArrow.svelte';
	import Rerun from './Icons/Rerun.svelte';

	let {
		step,
		posted,
		updatePosted
	}: {
		step: Step;
		posted: boolean;
		updatePosted: Function;
	} = $props();
	let finishedAt: string | null = $state(null);
	let posting: boolean = $state(false);
	let error: boolean = $state(false);
	let failedAt: boolean = $state(false);

	const {
		can_rerun: canRerun,
		status,
		id,
		finished_at: finished,
		failure,
		rerun_url: rerunUrl
	} = step;

	$effect(() => {
		dayjs.extend(localizedFormat);

		if (failure?.occurred_at) {
			failedAt = dayjs(failure.occurred_at).locale(localeDa).format('LLLL');
		}
		if (finished) {
			finishedAt = dayjs(finished).locale(localeDa).format('LLLL');
		}
	});

	function rerun() {
		if (rerunUrl !== null) {
			posting = true;
			error = false;

			fetch(rerunUrl, { method: 'POST' })
				.then((response) => response.json())
				.then(() => {
					updatePosted(true);
				})
				.catch(() => {
					error = true;
				})
				.finally(() => {
					posting = false;
				});
		}
	}
	// tailwind so verbose
	function getStatusClasses() {
		if (posted) return 'border-violet-600';

		switch (status) {
			case StepStatus.FAILED:
				return 'border-rose-700';
			case StepStatus.SUCCESS:
				return 'border-green-700';
			case StepStatus.PENDING:
				return 'border-neutral-400';
		}
	}
</script>

<div
	id={`popover-${id}`}
	class="anchor-position transition-all motion-reduce:transition-none top-[anchor(bottom)] [justify-self:anchor-center] bg-transparent [&:popover-open]:flex items-center flex-col"
	anchor={`anchor-${id}`}
	popover
>
	<DecorationalArrow {getStatusClasses} />
	<div
		class="{getStatusClasses()} border border-2 rounded-md p-4 dark:bg-gray-900 dark:text-white bg-white"
	>
		{#if posted}
			<div class="flex items-center">
				<Rerun className="h-4 w-4 mr-2" />
				<div class="py-1 font-thin">{t('The process was restarted')}</div>
			</div>
		{/if}
		{#if !posted}
			{#if failure}
				<div class="py-1 font-bold">
					{failure.message}
				</div>
				<div class="py-1 font-thin">
					{t('Error code')}
					{failure.code}
				</div>
			{/if}

			{#if finishedAt}
				<div class="py-1 font-thin">
					{t('Finished at')}
					{finishedAt}
				</div>
			{/if}
			{#if failedAt}
				<div class="py-1 font-thin">
					{t('Failed at')}
					{failedAt}
				</div>
			{/if}
			<!-- For consistency, the button is always visible on failed processes, and disabled if they cannot rerun -->
			{#if status === StepStatus.FAILED}
				<button
					onclick={() => rerun()}
					disabled={!canRerun || rerunUrl === null}
					type="button"
					class="flex flex-row items-center justify-center my-3 py-1 px-3 w-full disabled:cursor-default cursor-pointer p-1 disabled:bg-gray-300 hover:disabled:bg-gray-300 rounded-xs text-white bg-violet-600 hover:bg-violet-300"
				>
					{t('Rerun step')}
					{#if posting}
						<Spinner className="animate-spin h-4 w-4 ml-2 dark:text-white fill-gray-400" />
					{/if}
				</button>
			{/if}
			{#if error}
				<div class="py-1 font-bold max-w-[280px] flex items-center">
					<Bell className="h-8 w-8 mr-2" />
					{t('An error occurred when the process was restarted')}
				</div>
			{/if}
		{/if}
	</div>
</div>
