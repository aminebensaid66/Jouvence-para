# Background jobs

Jouvence Para jobs use the Action Scheduler API bundled with WooCommerce. The core plugin groups its actions under `jouvence-para` and does not call queue APIs until Action Scheduler has initialized.

## Registering a job

Register a `JobHandler` with the shared `JobsModule::queue()` service before enqueuing it. Pass that same queue service to modules that publish or handle jobs. Use a short stable name containing lowercase letters, digits, and underscores. Keep the serialized payload to small scalar values and stable references such as product, order, or event IDs. Do not put credentials, payment data, full webhook bodies, or unnecessary customer data in action arguments; administrators can inspect those arguments in Scheduled Actions.

Handlers receive a `JobContext` with the current attempt, retry limit, and optional non-secret idempotency key. Mark a job as requiring a key when duplicate delivery can repeat an externally visible effect; producers must reuse the same stable key for the same logical operation. The handler must enforce that key at the effect boundary, such as with a provider idempotency API or durable application state. Queue delivery is at least once, so the queue alone does not promise exactly-once side effects.

## Retry policy

Every job registration must supply an explicit `RetryPolicy` with its total attempts, initial delay, growth factor, and maximum delay. This keeps retry timing with the owning integration instead of imposing one policy on order, payment, stock, email, or synchronization work. After exhaustion, Action Scheduler marks the action failed and records its execution log; the core plugin also sends a structured `background_job_failed` event through the existing logger without including the exception message or payload.

## Inspecting and recovering failures

Administrators with WooCommerce management capability can open **Tools → Background jobs** to see the latest failed Jouvence Para actions and retry a recoverable action. Recovery creates a new action with the same payload and idempotency key and restarts the retry count at one. Diagnose the failure in the corresponding Scheduled Actions log first. The WooCommerce Scheduled Actions screen remains the source for action history and execution logs.

The queue uses Action Scheduler's normal runner. Ensure the site's configured WordPress cron or Action Scheduler runner is processing scheduled actions in production; otherwise queued work will remain pending.
