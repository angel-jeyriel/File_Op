# File Op — Implementation Plan

**Project:** File Op API  
**Status:** Planned  
**Architecture:** Laravel modular monolith with asynchronous media workers

## Project goal

Build a portfolio quality Laravel API that accepts image and video uploads, processes them asynchronously, and reports secure, measurable results. Use **File Op** as the product name and **File Op API** when referring to the API. Do not use the previous working name.

The first release covers API key authentication, private uploads, image conversion and resizing, video inspection with compatible MP4 remuxing, optional controlled transcoding, job status polling, temporary downloads, usage measurements, validation, tests, and API documentation. Audio/PDF processing, billing, a customer dashboard, public URL imports, and distributed workers are later work.

## Core design decisions

- Keep a modular Laravel application; run expensive media processing in queue workers.
- Separate processing from storage. Processors create and verify outputs; a storage service persists them through Laravel disks.
- Keep files private. Return short lived download links and scope all resources to the authenticated owner.
- Store byte counts and durations as numeric values. Keep the canonical disk and path private.
- Identify media from file contents, not the client filename or MIME declaration alone.
- Use random opaque public optimization IDs. Hash API key secrets at rest and display the secret once.
- Use distinct image quality and video CRF options with type and mode specific validation.
- Report conversion success and size savings separately; a valid output can be larger than its input.
- Make queue handling idempotent and clean temporary or orphaned outputs after failures.

## Data model

Use Laravel's `users` table and add:

- **api_keys:** owner, name, visible prefix, unique hash, optional scopes, last-used, expiry and revocation timestamps.
- **optimizations:** opaque public ID, owner and key IDs, idempotency key, lifecycle status, category, options, input/output byte counts, processing time, stable error code, and lifecycle timestamps.
- **stored_files:** optimization, original/output role, disk and path, original filename, MIME type, extension, size, metadata and optional SHA-256 checksum.
- **usage_records:** owner, key, optimization, input/output bytes, processing time and creation timestamp.

Add ownership and lifecycle indexes, and enforce idempotency with a database uniqueness constraint scoped to the caller/key. An optimization initially has one original and at most one output, while the file table should allow future variants.

## API surface

Version routes under `/api/v1`. Require a bearer API key on service endpoints.

| Method | Endpoint | Initial delivery |
|---|---|---|
| `POST` | `/optimizations` | Yes: upload and enqueue |
| `GET` | `/optimizations/{public_id}` | Yes: poll state and retrieve results |
| `GET` | `/files/{public_id}/download` | Yes: authorized, expiring download |
| `GET` | `/formats` | Yes: supported inputs, outputs and options |
| `GET` | `/usage` | Yes: basic usage summary |
| `GET` | `/optimizations` | Later: list jobs |
| `POST` | `/optimizations/{public_id}/cancel` | Later: queued-job cancellation |
| `GET` | `/files/{public_id}` | Later: file metadata |
| `DELETE` | `/files/{public_id}` | Later: owned-file deletion |

The create endpoint accepts multipart file data and validated type-specific options. Return `202 Accepted` with an opaque ID and status URL when queued. Poll responses include status, original/output metadata, byte savings, processing time, and an expiring download URL on completion. Use a consistent JSON error shape and appropriate 400, 401, 403, 404, 413, 422, 429, and 500 responses. Processing errors after acceptance update the job to `failed`.

## Processing lifecycle

`pending → queued → processing → completed | failed`; queued jobs may later transition to `cancelled`.

The job claims work atomically, records its start time, loads the private source, inspects actual media, runs the selected processor under resource/time limits, verifies the output, stores it, records usage and completion, and cleans temporary files in a `finally` block. Retry transient infrastructure failures selectively; invalid media and unsupported settings are permanent failures. Use idempotent output paths and reconciliation/cleanup to handle worker interruption.

### Image processing

Use Intervention Image with a configured driver. Validate actual format and pixel/dimension limits, apply orientation correction where supported, preserve aspect ratio for ordinary resizing, and crop only on explicit request. Encode to an explicitly requested supported output format (WebP as a useful initial default), then verify the result is nonempty and decodable. Treat animation support as format/driver dependent and report it accurately.

### Video processing

Use FFprobe to inspect container, streams, codecs, dimensions, frame rate, duration and bitrate. For compatible streams, remux to MP4 with stream copy and `+faststart`. When requested, transcode with a controlled H.264/AAC profile and validated resolution, CRF, preset, and timeout. Invoke FFmpeg through Symfony Process argument arrays. Probe and verify outputs. A failed optional remux may preserve the valid original as an unchanged result; an explicitly requested transcode failure must fail the job.

## Configuration and local environment

Create application configuration for upload limits, image quality/dimension limits, FFmpeg/FFprobe binaries and timeouts, video defaults, queue behavior, and retention. Keep secrets in environment variables. Tune limits after measurement; coordinate Laravel validation with PHP, web server, temporary disk, and worker limits.

Provide Docker Compose for a compatible PHP application/worker image with FFmpeg, MySQL, and Redis. Keep local storage private and preserve the option to use private S3-compatible storage in deployment. Document that a queue worker must run for jobs to leave the queued state.

## Delivery phases

### Phase 1 — Foundation

- Confirm the Laravel runtime and generated project guidance; configure app identity as File Op.
- Establish environment/configuration conventions, private local storage, database, Redis queue settings, response conventions, and formatting/static analysis expectations.
- Add concise setup documentation and baseline project checks.

**Exit:** application boots locally, configuration is documented, and migrations can run from a clean database.

### Phase 2 — Schema and API key authentication

- Add migrations, models, relationships, factories, indexes, and idempotency constraints.
- Implement secure API key generation, hashing, bearer authentication, expiry/revocation checks, last-used tracking, and ownership context.
- Add an appropriate local-only key creation/seeding workflow without committing a usable secret.

**Exit:** authenticated and unauthenticated requests behave predictably; keys cannot read another owner's records.

### Phase 3 — Image optimization MVP

- Define processor contract, typed result, inspection/policy boundaries, and domain failures.
- Implement image validation, conversion, resizing, private input/output persistence, metadata, and size measurement.
- Add formats endpoint support for implemented capabilities.

**Exit:** a valid image can be transformed and verified through the service layer with honest measurements.

### Phase 4 — Asynchronous workflow

- Implement upload request validation, private source storage, optimization creation, queue dispatch, status resource, and worker lifecycle.
- Add atomic job claiming, transient retry policy, failure recording, temporary cleanup, and download authorization/signing.
- Ensure duplicate/idempotent requests cannot create concurrent duplicate work.

**Exit:** an authenticated image upload returns 202, can be polled to completion, and can be downloaded securely.

### Phase 5 — Video support

- Add FFprobe inspection and metadata validation with configurable limits.
- Implement compatible MP4 remuxing and optional constrained transcode using safe process invocation.
- Verify outputs and distinguish unchanged/no-op results from requested operation failures.

**Exit:** supported videos are inspected and remuxed/transcoded according to explicit options; failures are recorded safely.

### Phase 6 — API usability and safeguards

- Complete usage reporting and consistent errors; implement optimization listing and queued cancellation as appropriate.
- Add idempotency, rate limits/quotas, retention cleanup, security checks, and OpenAPI documentation.
- Ensure all file access remains owner-scoped and signed links expire.

**Exit:** API behavior and limits are documented and observable for a client integration.

### Phase 7 — Portfolio and deployment

- Finish Docker development setup, worker process management, health checks, structured logs, and deployment guide.
- Document private S3-compatible storage, secrets, queue monitoring, cleanup, and worker isolation.
- Add CI and measured benchmarks once supported formats and target limits are settled.

**Exit:** a reviewer can reproduce the service locally and understand how it is deployed and operated.

## Verification strategy

Cover API key authentication and ownership, request validation, idempotency, job state transitions, private downloads, storage failures, image transformations, processor failures, video inspection/remux/transcode behavior, usage accounting, and cleanup. Use fakes for queues and storage in focused tests, plus a small set of real media integration checks where FFmpeg and image drivers are available. Include API documentation examples and verify them against the implemented routes.

## Portfolio deliverables

- Clear README with purpose, architecture, setup, supported formats/options, API examples, and known limits.
- OpenAPI specification and example request/response payloads.
- Docker Compose development environment and reproducible queue worker instructions.
- CI workflow, automated tests, and benchmark methodology/results for representative media.
- No committed secrets or publicly exposed source files.

## First implementation milestone

Begin with the File Op identity, Laravel foundation, ownership/data model, API key authentication, and processor contract. Then build the complete asynchronous image path before adding video processing. This establishes job, storage, security, and measurement behavior that both processors can share.
