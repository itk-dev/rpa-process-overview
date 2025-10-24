// Not sure I've got the types, but theyll probably change when we get the proper api so I wont bother yet

type RowColumnType = 'step' | 'text';
type MetaType = 'meta.branch' | 'meta.name' | 'meta.cpr';
export type Status = 'success' | 'failed' | 'pending';

export type Column = {
	data: MetaType;
	label: string;
	id: number;
	type: RowColumnType;
};

type Failure = {
	code: number;
	message: string;
	occurred_at: Date;
	retryable: Boolean;
};

export type Step = {
	type: RowColumnType;
	value: string | null;
	rerun_url: string | null;
	created_at: Date | null;
	occurred_at: Date | null;
	started_at: Date | null;
	finished_at: Date | null;
	updated_at: Date | null;
	failure: Failure | null;
	id: Number | null;
	step_id: Number | null;
	step_index: Number | null;
	run_id: Number | null;
	status: Status;
	can_rerun: boolean | null;
};

// @todo This can be any object (the the keys and the value types are not static).
type MetaDataItem = {
	branch: string;
	cpr: string;
	name: string;
};

type Item = {
	id: number;
	meta: MetaDataItem;
	process_id: number;
	status: Status;
	steps: Step[];
};

export type RawData = {
	page: number;
	pages: number;
	size: number;
	total: number;
	items: Item[];
};

export type ProgressData = {
	columns: Column[];
	// @todo The raw run data will be removed shortly.
	data: RawData[];
	rows: Array<Array<Step>>;
};

export type Messages = {
	[key: string]: string;
};

export type OverviewConfig = {
	messages: Messages;
	data_url: string;
	page_size: string;
};

export type SearchConfig = {
	messages: Messages;
	search_url: string;
	minimum_search_query_length: number;
};
