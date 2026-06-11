<?php
/**
 * Filter bar component — renders chip groups.
 *
 * Required variables:
 *   $filterBarGroups  array   Output of FilterBar::toView()
 *
 * Each group: ['label', 'param', 'current', 'chips']
 * Each chip:  ['value', 'label', 'count', 'active', 'url']
 */
if (empty($filterBarGroups)) {
    return;
}
?>
<div class="filter-bar mb-3">
    <?php foreach ($filterBarGroups as $group): ?>
        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-2">
            <?php if ($group['label'] !== ''): ?>
                <span class="text-muted small fw-semibold me-1" style="min-width: max-content;">
                    <?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?>:
                </span>
            <?php endif; ?>
            <?php foreach ($group['chips'] as $chip): ?>
                <a href="<?= htmlspecialchars(\Core\Url::to($chip['url']), ENT_QUOTES, 'UTF-8') ?>"
                   class="badge rounded-pill text-decoration-none <?= $chip['active'] ? 'bg-primary text-white' : 'bg-light text-dark border' ?>"
                   style="font-size: 0.78rem; font-weight: <?= $chip['active'] ? '600' : '400' ?>;">
                    <?= htmlspecialchars($chip['label'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($chip['count'] !== null): ?>
                        <span class="ms-1 opacity-75"><?= (int) $chip['count'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
