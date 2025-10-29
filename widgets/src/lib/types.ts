// Not sure I've got the types, but theyll probably change when we get the proper api so I wont bother yet

type RowColumnType = 'meta' | 'step';
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
	retryable: boolean;
};

export type Step = {
	raw_value_url: string | null;
	type: RowColumnType;
	value: string | null;
	// rerun_url is not set if user cannot rerun steps.
	rerun_url?: string | null;
	created_at: Date | null;
	occurred_at: Date | null;
	started_at: Date | null;
	finished_at: Date | null;
	updated_at: Date | null;
	failure: Failure | null;
	id: number | null;
	step_id: number | null;
	step_index: number | null;
	run_id: number | null;
	status: Status;
	can_rerun: boolean | null;
};

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
	data: RawData[];
	rows: Array<Array<Step>>;
};

export type Messages = {
	[key: string]: string;
};

export type OverviewConfig = {
	messages: Messages;
	data_url: string;
	title: string;
	page_size: string;
};

export type SearchConfig = {
	messages: Messages;
	search_url: string;
	process_id: string;
	minimum_search_query_length: number;
};
