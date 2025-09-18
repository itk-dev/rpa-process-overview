<script lang="ts">
	const config = (() => {
		try {
			return JSON.parse(document.getElementById('ProcessOverview')?.dataset.config || '{}');
		} catch (error) {
			return {};
		}
	})();

	const t = (text) => text;

	const url = new URL(document.location.href);
	let currentPage = $state(
		(() => {
			const i = parseInt(url.searchParams.get('page') ?? '1', 10);
			return isNaN(i) ? 1 : i;
		})()
	);

	//	let maxCurrentPage = currentPage;
	let pages = $state({ length: currentPage });
	// $effect(() => {
	// 	maxCurrentPage = Math.max(maxCurrentPage, currentPage);
	// });

	const setPage = (page: number, event: Event): void => {
		// @todo Is this really necessary?
		event.preventDefault();

		currentPage = page;
		// maxCurrentPage =
		pages = { length: Math.max(pages.length, currentPage) };

		// Update page URL without adding history entry.
		history.replaceState({}, '', getPageUrl(currentPage));
	};

	const getPageUrl = (page: number) => {
		url.searchParams.set('page', page.toFixed());

		return url.toString();
	};

	let data: any[] | null = $state(null);
	let fetching = $state(true);
	let header: any[] = $state(null);

	const buildHeader = () => {
		if (null === header) {
			header = data.columns.map((cell) => cell);
		}
	};

	$effect(() => {
		const pageUrl = new URL(document.location.href);
		pageUrl.searchParams.set('page', currentPage.toString());
		history.replaceState({}, '', pageUrl);

		const url = new URL(config.data_url, document.location.href);
		url.searchParams.set('page', currentPage.toString());

		fetching = true;
		//data = null;
		setTimeout(() => {
			fetch(url.toString())
				.then((response) => response.json())
				.then((result) => {
					data = result;
					buildHeader();
					fetching = false;
				});
		}, 1000);
	});
</script>

{#if null === data}
	<div class="alert alert-info">Fetching data …</div>
{:else if 0 === data.length}
	<div class="alert alert-warning">No data</div>
{:else}
	<table class="table">
		{#if header}
			<thead>
				<tr>
					{#each header as column}
						<th scope="col">{column.label}</th>
					{/each}
				</tr>
			</thead>
		{/if}
		{#if data}
			<tbody class={{ fetching }}>
				{#if data.rows.length > 0}
					{#each data.rows as row}
						<tr>
							{#each row as cell}
								<td>
									{cell.value ?? cell.status ?? '👻'}
								</td>
							{/each}
						</tr>
					{/each}
				{:else}
					<tr>
						<td colspan={header.length}>empty</td>
					</tr>
				{/if}
			</tbody>
		{/if}
	</table>

	<pre>{JSON.stringify([currentPage, pages])}</pre>
	<nav aria-label="Page navigation">
		<ul class="pagination">
			<li class={['page-item', { disabled: 1 === currentPage }]}>
				<a
					href={getPageUrl(currentPage - 1)}
					class="page-link"
					onclick={(event) => setPage(currentPage - 1, event)}>{t('Prev')}</a
				>
			</li>
			{#each pages, page}
				<li class={['page-item', { active: page + 1 === currentPage }]}>
					<a
						href={getPageUrl(page + 1)}
						class="page-link"
						onclick={(event) => setPage(page + 1, event)}>{page + 1}</a
					>
				</li>
			{/each}
			<li class={['page-item', { disabled: 0 === data.rows.length }]}>
				<a
					href={getPageUrl(currentPage + 1)}
					class="page-link"
					onclick={(event) => setPage(currentPage + 1, event)}>{t('Next')}</a
				>
			</li>
		</ul>
	</nav>
{/if}

<details>
	<summary>Config and data</summary>

	<code><pre>{JSON.stringify({ config, data }, null, 2)}</pre></code>
</details>

<style>
	.fetching {
		opacity: 0.5;
	}
</style>
