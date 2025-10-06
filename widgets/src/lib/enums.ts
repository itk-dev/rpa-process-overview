const Status = {
	FAILED: 'FAILED',
	PENDING: 'PENDING',
	SUCCESS: 'SUCCESS'
} as const;

Object.freeze(Status);

export default Status;
