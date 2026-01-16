# Phase 6: Offline-First & Sync

**Goal**: Enable offline capability.

## 6.1 Sync Mechanics (Design)

-   [ ] Concept: "dirty" flags or "mutation log".
-   [ ] **Decision**: Append-only Mutation Log approach per specs.
-   [ ] Client Side: (Assuming PWA/Mobile) needs to replicate schema.

## 6.2 Sync Module (Server Side)

-   [ ] Create `Modules/Sync`.
-   [ ] `SyncEndpoint`: Receives a batch of mutations (UUID, payload, timestamp).
-   [ ] `ConflictResolver`: First-write-wins or Version-Vector (Vector Clock).
    -   _Simpler start_: Last-Write-Wins based on robust server timestamp if feasible, but specs mention version vectors. We will support version vectors.

## 6.3 API Layer

-   [ ] `GET /sync/pull`: Returns changes since Last-Known-Version.
-   [ ] `POST /sync/push`: Accepts new changes.

## 6.4 Testing

-   [ ] Simulation: Submit same change from two devices. Verify implementation of conflict resolution.
-   [ ] Load Test: Syncing large batches of ledger entries.
