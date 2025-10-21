// Enums to match https://github.com/AAK-MBU/Process_Dashboard_API/blob/main/app/models/enums.py

export const StepStatus = {
	FAILED: 'failed',
	PENDING: 'pending',
	SUCCESS: 'success'
} as const;

export const ProjectStatus = {
	PENDING: 'pending',
	RUNNING: 'running',
	COMPLETED: 'completed',
	FAILED: 'failed'
} as const;
