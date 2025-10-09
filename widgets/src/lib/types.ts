// Not sure I've got the types, but theyll probably change when we get the proper api so I wont bother yet

type RowColumnType = 'step' | 'text';
type MetaType = 'meta.branch' | 'meta.name' | 'meta.cpr';
export type Status = 'success' | 'failed' | 'pending' | 'running';

export type Column = {
	data: MetaType;
	label: string;
	type: RowColumnType;
};
type Failure = {
	code: Number;
	message: string;
	occurred_at: Date;
	retryable: Boolean;
};

export type Step = {
	type: RowColumnType;
	value: string | null;
	created_at: Date | null;
	started_at: Date | null;
	finished_at: Date | null;
	updated_at: Date | null;
	failure: Failure | null;
	id: Number | null;
	step_id: Number | null;
	step_index: Number | null;
	run_id: Number | null;
	status: Status;
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
	page: Number;
	pages: Number;
	size: Number;
	total: Number;
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
};

export type SearchConfig = {
	messages: Messages;
	search_url: string;
	min_search_limit: number;
};
