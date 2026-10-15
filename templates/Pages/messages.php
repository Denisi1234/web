<?php
$this->assign('title', 'Messages - FastNet Stays');
$active = [
    'partner_id' => (int)($activeHostId ?? 0),
    'partner_name' => (string)($activeThread['hostName'] ?? ''),
    'partner_role' => (string)($activeThread['partnerRole'] ?? ''),
    'lodge_name' => (string)($lodgeName ?? ''),
    'booking_code' => (string)($bookingCode ?? ''),
    'booking' => (isset($activeBooking) && is_array($activeBooking)) ? $activeBooking : [],
    'property' => (isset($activeLodge) && is_array($activeLodge)) ? $activeLodge : [],
    'is_self' => !empty($activeThread['isSelfChat']),
];
?>
<?= $this->element('navbar') ?>
<?= $this->Html->css('/assets/css/my-booking.css') ?>
<?= $this->element('Messaging/chat', [
    'threads' => $threads ?? [],
    'active' => $active,
    'initialMessages' => $initialMessages ?? [],
    'currentUserId' => (int)($currentUserId ?? 0),
    'isHost' => false,
    'backUrl' => '/my-booking',
    'sendEndpoint' => '/messages/send',
    'historyEndpoint' => '/messages/history',
    'threadBase' => '/messages',
    'threadParam' => 'host_id',
]) ?>
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
