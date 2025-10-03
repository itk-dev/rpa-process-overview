<script lang="ts">
	const config = (() => {
		try {
			return JSON.parse(document.getElementById('ProcessSearch')?.dataset.config || '{}');
		} catch (error) {
			return {};
		}
	})();

	let query = $state('');
	let parsedQuery = $state('');
	let data: any[] | null = $state(null);

	$effect(() => {
		parsedQuery = query.trim();

		if (parsedQuery) {
			const pageUrl = new URL(document.location.href);
			pageUrl.searchParams.set('q', parsedQuery);
			history.replaceState({}, '', pageUrl);

			const url = new URL(config.search_url, document.location.href);
			url.searchParams.set('q', parsedQuery);

			data = null;
			fetch(url.toString())
				.then((response) => response.json())
				.then((result) => (data = result));
		}
	});
</script>

<form>
	<div class="mb-3">
		<input
			type="search"
			class="form-control"
			placeholder="Search"
			aria-label="Search"
			bind:value={query}
		/>
	</div>
</form>

{#if parsedQuery}
	{#if 0 === (data?.rows ?? []).length}
		<h1>No results matching <em class="query">{parsedQuery}</em></h1>
	{:else if 1 === data?.rows?.length}
		<h1>One result matching <em class="query">{parsedQuery}</em></h1>
	{:else}
		<h1>{data?.rows?.length} results matching <em class="query">{parsedQuery}</em></h1>
	{/if}

	<code><pre>{JSON.stringify(data?.rows)}</pre></code>
{:else}
	<p>Enter a non-empty query.</p>
{/if}

<details>
	<summary>Config and data</summary>

	<code><pre>{JSON.stringify({ config, data }, null, 2)}</pre></code>
</details>
