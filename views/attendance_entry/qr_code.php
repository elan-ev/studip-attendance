<?php
$meta = $messages['meta'] ?? [];
$courseName = $meta['course_name'] ?? _('Unbekannte Veranstaltung');
$sessionDate = null;

if (!empty($meta['session_date'])) {
    $startDate = $meta['session_date'];
    $endDate = $meta['session_enddate'] ?? null;

    if ($endDate && date('Y-m-d', $startDate) === date('Y-m-d', $endDate)) {
        $sessionDate = sprintf(
            '%s, %s – %s Uhr',
            date('d.m.Y', $startDate),
            date('H:i', $startDate),
            date('H:i', $endDate)
        );
    } elseif ($endDate) {
        $sessionDate = sprintf(
            '%s Uhr – %s Uhr',
            date('d.m.Y, H:i', $startDate),
            date('d.m.Y, H:i', $endDate)
        );
    } else {
        $sessionDate = date('d.m.Y, H:i', $startDate) . ' Uhr';
    }
}
$isSuccess = !empty($messages['success']);
$isError = !empty($messages['error']);
?>

<style>
.checkin-feedback-wrapper {
    max-width: 540px;
    margin: 2rem auto;
    padding: 0 1rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.checkin-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.checkin-header {
    background: #f8fafc;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: center;
}

.checkin-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
}

.checkin-header h2 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 500;
    color: #64748b;
}

.checkin-body {
    padding: 2rem 1.5rem;
    text-align: center;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    margin-bottom: 1.25rem;
}

.status-badge.success {
    background-color: #dcfce7;
    color: #166534;
}

.status-badge.error {
    background-color: #fee2e2;
    color: #991b1b;
}

.status-badge svg {
    width: 32px;
    height: 32px;
    stroke-width: 2.5;
    stroke: currentColor;
}

.status-message {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    line-height: 1.5;
}

.status-message.success {
    color: #15803d;
}

.status-message.error {
    color: #b91c1c;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

@media (max-width: 480px) {
    .checkin-feedback-wrapper {
        margin: 1rem auto;
        padding: 0 0.75rem;
    }
    
    .checkin-header {
        padding: 1.25rem 1rem;
    }
    
    .checkin-header h1 {
        font-size: 1.1rem;
    }
    
    .checkin-body {
        padding: 1.5rem 1rem;
    }
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
<main class="checkin-feedback-wrapper">
    <article class="checkin-card">
        <header class="checkin-header">
            <h1><?= htmlReady($courseName) ?></h1>
            <?php if ($sessionDate): ?>
                <h2><?= htmlReady($sessionDate) ?></h2>
            <?php endif; ?>
        </header>

        <div class="checkin-body">
            <?php if ($isSuccess): ?>
                <div class="status-badge success" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div role="status" aria-live="polite" class="status-message success">
                    <span class="sr-only"><?= htmlReady(_('Erfolg:')) ?> </span>
                    <?= htmlReady($messages['success']) ?>
                </div>
            <?php elseif ($isError): ?>
                <div class="status-badge error" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div role="alert" aria-live="assertive" class="status-message error">
                    <span class="sr-only"><?= htmlReady(_('Fehler:')) ?> </span>
                    <?= htmlReady($messages['error']) ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
</main>