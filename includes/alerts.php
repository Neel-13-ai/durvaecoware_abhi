<?php
/**
 * Flash Alert Component
 * Durva Eco Ware
 */

$flashSuccess = flash('success');
$flashError = flash('error');
$flashWarning = flash('warning');
?>
<div class="alert-container">
  <?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="alert">
      <span><?= e($flashSuccess['message']) ?></span>
      <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:16px;">&times;</button>
    </div>
  <?php endif; ?>

  <?php if ($flashError): ?>
    <div class="alert alert-error" role="alert">
      <span><?= e($flashError['message']) ?></span>
      <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:16px;">&times;</button>
    </div>
  <?php endif; ?>

  <?php if ($flashWarning): ?>
    <div class="alert alert-warning" role="alert">
      <span><?= e($flashWarning['message']) ?></span>
      <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:16px;">&times;</button>
    </div>
  <?php endif; ?>
</div>
