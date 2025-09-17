<script lang="ts">
    import { page } from '$app/state';
    import {goto} from "$app/navigation";

    const config = (() => {
        try {
            return JSON.parse(document.getElementById('ProcessOverview')?.dataset.config || '{}')
        } catch (error) {
            return {}
        }
    })()

    const url = page.url;
    let currentPage = $state((() => {
        const i = parseInt(url.searchParams.get('page') ?? '1', 10)
        return isNaN(i) ? 1 : i
    })())

const pages = [1,2,3,
    // @todo Handle "prev" and "next"
    // 'next',
]

    const setPage = (page: number, event: Event): void => {
        // @todo Is this really necessary?
        event.preventDefault()

        currentPage = page
        goto(getPageUrl(page))
    }

    const getPageUrl = (page: number) => {
        url.searchParams.set('page', page.toFixed());

        return url.toString();
    }

    let data: any[]|null = $state(null)

    // goto('?page=87')

    $effect(() => {
        const url = new URL(config.data_url, document.location.href);
        url.searchParams.set('page', currentPage.toString());
        console.log(url.toString());
        data = null
        fetch(url.toString())
            .then(response => response.json())
            .then(result => data = result)
    })
</script>

{#if null === data}
    <div class="alert alert-info">Fetching data …</div>
{:else if 0 === data.length}
    <div class="alert alert-warning">No data</div>
{:else}
    <table class="table">
        <thead>
        <tr>
    {#each data.columns as column}
        <th scope="col">{column.label}</th>
    {/each}
        </tr>
        </thead>
        <tbody>
        {#each data.rows as row}
            <tr>
            {#each row as cell}
                <td>
                    {cell.value ?? cell.status ?? '--'}
                </td>
            {/each}
            </tr>
        {/each}
        </tbody>
    </table>

    <nav aria-label="Page navigation example">
        <ul class="pagination">
            {#each pages as page}
            <li class={['page-item', {stuff: true, active: page === currentPage}]}>
                <a href={getPageUrl(page)} class="page-link" onclick={(event) => setPage(page, event)}>{page}</a>
            </li>
                {/each}
        </ul>
    </nav>

    <details>
        <summary>Data</summary>

    <code><pre>{JSON.stringify(data, null, 2)}</pre></code>
    </details>
{/if}

