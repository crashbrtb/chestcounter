<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
$class = 'alert alert-info alert-dismissible fade show';
if (!empty($params['class'])) {
    if (is_array($params['class'])) {
        $class = implode(' ', array_merge(['alert', 'alert-info', 'alert-dismissible', 'fade', 'show'], $params['class']));
    } else {
        $class .= ' ' . $params['class'];
    }
}
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="<?= h($class) ?>" role="alert">
    <i class="icon fas fa-info"></i> <?= $message ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
