---
paths:
  - 'app/Events/**,app/Models/Notification.php,routes/developer.php,resources/views/partials/head.blade.php'
---

# Partials

## Notification broadcasting and developer page constraints
NotificationCreated implements ShouldBroadcast (database queue), not ShouldBroadcastNow — creating a notification must never fail when the Reverb server is down. Realtime delivery depends on queue:listen + reverb:start, both run by `composer run dev`. Echo private-channel auth requires the csrf-token meta tag in partials/head.blade.php — do not remove it. The /developer routes are guarded by EnsureLocalEnvironment (404 outside local/testing) by design, not by a permission.
