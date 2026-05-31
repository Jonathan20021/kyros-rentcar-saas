<?php
/**
 * Reusable contact action chips (tel, WhatsApp, email).
 *
 * Usage:
 *   <?= View::renderPartial('_partials/contact_actions', [
 *     'phone'    => $customer['phone'],
 *     'whatsapp' => $customer['whatsapp'] ?? $customer['phone'],
 *     'email'    => $customer['email'],
 *     'country'  => $tenant['country'] ?? 'DO',  // for default code
 *     'message'  => 'Hola ' . $customer['first_name'] . ', te escribo de Kyros Rent Car...',
 *     'size'     => 'sm',  // 'sm' | 'md'
 *   ]) ?>
 */
$phone    = $phone    ?? null;
$whatsapp = $whatsapp ?? $phone;
$email    = $email    ?? null;
$country  = strtoupper($country ?? 'DO');
$message  = $message  ?? '';
$size     = $size     ?? 'md';

$cc = $country === 'CO' ? '+57' : '+1';
$tel = tel_link($phone, $cc);
$wa  = wa_link($whatsapp, $message, $cc);
$ml  = mail_link($email);

$btn = $size === 'sm'
  ? 'w-7 h-7 rounded-lg text-[13px]'
  : 'w-9 h-9 rounded-xl text-[16px]';
$icon = $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4';
?>
<?php if ($tel || $wa || $ml): ?>
<div class="inline-flex items-center gap-1">
  <?php if ($wa): ?>
    <a href="<?= e($wa) ?>" target="_blank" rel="noopener"
       class="inline-flex items-center justify-center <?= $btn ?> bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition"
       title="WhatsApp: <?= e($whatsapp) ?>" aria-label="WhatsApp">
      <i data-lucide="message-circle" class="<?= $icon ?>"></i>
    </a>
  <?php endif; ?>
  <?php if ($tel): ?>
    <a href="<?= e($tel) ?>"
       class="inline-flex items-center justify-center <?= $btn ?> bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition"
       title="Llamar: <?= e($phone) ?>" aria-label="Llamar">
      <i data-lucide="phone" class="<?= $icon ?>"></i>
    </a>
  <?php endif; ?>
  <?php if ($ml): ?>
    <a href="<?= e($ml) ?>"
       class="inline-flex items-center justify-center <?= $btn ?> bg-slate-100 text-slate-500 hover:bg-slate-200 transition"
       title="Email: <?= e($email) ?>" aria-label="Email">
      <i data-lucide="mail" class="<?= $icon ?>"></i>
    </a>
  <?php endif; ?>
</div>
<?php endif; ?>
