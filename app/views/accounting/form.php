<?php
    $record = $record ?? [];
    $fields = $fields ?? [];
    $mode = $mode ?? 'create';
    $title = $title ?? 'Record';
    $section = $section ?? 'index';
    $submitAction = $submitAction ?? $section;
    $recordId = $recordId ?? ($record['id'] ?? null);
    $formAction = '/index.php?controller=accounting&action=' . rawurlencode($submitAction);
    if ($mode === 'edit' && $recordId) {
        $formAction .= '&id=' . (int) $recordId;
    }
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0"><?= htmlspecialchars($title) ?></h2>
        <a href="/index.php?controller=accounting&action=<?= htmlspecialchars($section) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <?php foreach ($fields as $field): ?>
                <?php $inputName = $field['name']; $value = $record[$inputName] ?? ''; ?>
                <div class="mb-3">
                    <label for="<?= htmlspecialchars($inputName) ?>" class="form-label">
                        <?= htmlspecialchars($field['label'] ?? ucfirst($inputName)) ?>
                        <?= !empty($field['required']) ? '<span class="text-danger">*</span>' : '' ?>
                    </label>

                    <?php if (($field['type'] ?? 'text') === 'select'): ?>
                        <select id="<?= htmlspecialchars($inputName) ?>" name="<?= htmlspecialchars($inputName) ?>" class="form-select" <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php foreach ($field['options'] ?? [] as $option): ?>
                                <option value="<?= htmlspecialchars((string) $option) ?>" <?= (string) $value === (string) $option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst((string) $option)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif (($field['type'] ?? 'text') === 'textarea'): ?>
                        <textarea id="<?= htmlspecialchars($inputName) ?>" name="<?= htmlspecialchars($inputName) ?>" class="form-control" rows="4" <?= !empty($field['required']) ? 'required' : '' ?>><?= htmlspecialchars((string) $value) ?></textarea>
                    <?php else: ?>
                        <input
                            id="<?= htmlspecialchars($inputName) ?>"
                            name="<?= htmlspecialchars($inputName) ?>"
                            type="<?= htmlspecialchars($field['type'] ?? 'text') ?>"
                            class="form-control"
                            value="<?= htmlspecialchars((string) $value) ?>"
                            step="<?= htmlspecialchars((string) ($field['step'] ?? '')) ?>"
                            <?= !empty($field['required']) ? 'required' : '' ?>
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="/index.php?controller=accounting&action=<?= htmlspecialchars($section) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
