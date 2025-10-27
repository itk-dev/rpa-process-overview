<script lang="ts">
	let {
		getStatusClasses
	}: {
		getStatusClasses: Function;
	} = $props();
</script>

<div
	class="w-0 h-0 border-x-15 border-x-transparent border-b-15
			{getStatusClasses()}"
></div>
