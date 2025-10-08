// Enums to match https://github.com/AAK-MBU/Process_Dashboard_API/blob/main/app/models/enums.py

const StepStatus = {
	FAILED: 'failed',
	PENDING: 'pending',
	RUNNING: 'running',
	SUCCESS: 'success',
} as const;

Object.freeze(StepStatus);

const ProjectStatus = {
    PENDING: "pending",
    RUNNING : "running",
    COMPLETED : "completed",
    FAILED : "failed",
} as const;

Object.freeze(ProjectStatus);

// @todo How do I export both StepStatus as ProjectStatus?

export default StepStatus;
