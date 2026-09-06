<?php
$historys = [
    [
        'number' => '01', 
        'id' => 'BK32154', 
        'date' => '10 Sep 2026', 
        'status' => 'Paid', 
        'class' => 'success', 
        'amount' => '$240', 
    ],
    [
        'number' => '02', 
        'id' => 'BK32155', 
        'date' => '08 Aug 2026', 
        'status' => 'UnPaid', 
        'class' => 'warning', 
        'amount' => '$240', 
    ],
    [
        'number' => '03', 
        'id' => 'BK32156', 
        'date' => '10 Aug 2026', 
        'status' => 'Hold', 
        'class' => 'info', 
        'amount' => '$240', 
    ],
    [
        'number' => '04', 
        'id' => 'BK32157', 
        'date' => '22 Jul 2026', 
        'status' => 'completed', 
        'class' => 'seegreen', 
        'amount' => '$240', 
    ],
    [
        'number' => '05', 
        'id' => 'BK32158', 
        'date' => '16 Jun 2026', 
        'status' => 'cancel', 
        'class' => 'danger', 
        'amount' => '$240', 
    ],
    [
        'number' => '06', 
        'id' => 'BK32159', 
        'date' => '20 May 2026', 
        'status' => 'hold', 
        'class' => 'info', 
        'amount' => '$240', 
    ],
    [
        'number' => '07', 
        'id' => 'BK32160', 
        'date' => '18 Apr 2026', 
        'status' => 'completed', 
        'class' => 'seegreen', 
        'amount' => '$240', 
    ]
];
?>

<?php foreach ($historys as $item): ?>
    <tr>
        <th><?= h($item['number']) ?></th>
        <td><?= h($item['id']) ?></td>
        <td><?= h($item['date']) ?></td>
        <td><span class="badge bg-light-<?= h($item['class']) ?> text-<?= h($item['class']) ?> fw-medium text-uppercase"><?= h($item['status']) ?></span></td>
        <td><span class="text-md fw-medium text-dark"><?= h($item['amount']) ?></span></td>
    </tr>
<?php endforeach; ?>