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

	let {
		step
	}: {
		step: Step;
	} = $props();
	let updatedAt: string = $state('');
	let posting: boolean = $state(false);
	let error: boolean = $state(false);

	const { can_rerun, status, id, updated_at, failure, rerun_url } = step;
	
	$effect(() => {
		dayjs.extend(localizedFormat);
		updatedAt = dayjs(updated_at).locale(localeDa).format('LLLL');
	});

	function rerun() {
		posting = true;
		error = false;
		fetch(rerun_url, { method: 'POST' })
			.then((response) => response.json())
			.then((data) => {
				// todo add successful state when sure what api returns
			})
			.catch((e) => {
				error = true;
			})
			.finally(() => {
				posting = false;
			});
	}

	// tailwind so verbose
	function getPopverClasses() {
		return `border border-2 rounded-md p-4 dark:bg-gray-900 dark:text-white ${
			status === StepStatus.SUCCESS
				? 'border-green-700 dark:bg-green-100 bg-white'
				: status === StepStatus.FAILED
					? 'border-rose-700 dark:bg-rose-100 bg-white'
					: 'border-neutral-400 dark:bg-neutral-100 bg-white'
		}`;
	}
</script>

<div
	id={`popover-${id}`}
	class="anchor-position bg-transparent [&:popover-open]:flex items-center flex-col"
	anchor={`anchor-${id}`}
	popover
>
	<DecorationalArrow {status} />
	<div class={getPopverClasses()}>
		{#if failure}
			<div class="py-1 font-bold">
				{failure.message}
			</div>
		{/if}
		<div class="py-1 font-thin">
			{t('Updated at')}
			{updatedAt}
		</div>
		<!-- For consistency, the button is always visible on failed processes, and disabled if they cannot rerun -->
		{#if status === StepStatus.FAILED}
			<button
				onclick={() => rerun()}
				disabled={!can_rerun}
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
	</div>
</div>

<style scoped>
	.anchor-position {
		top: anchor(bottom);
		justify-self: anchor-center;
	}
</style>
