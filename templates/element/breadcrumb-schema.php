<?php
/**
 * Invisible BreadcrumbList structured data.
 *
 * The visible "Home › …" breadcrumb strip was removed sitewide on request.
 * This keeps the schema.org markup that search engines read, with no visual
 * footprint: Bootstrap's .visually-hidden clips it to a 1px box.
 *
 * Usage:  <?= $this->element('breadcrumb-schema', ['label' => 'About us']) ?>
 *
 * @var string $label  Human-readable name of the current page.
 */
$label = $label ?? 'Page';
?>
<nav class="visually-hidden" aria-label="Breadcrumb">
	<ol class="breadcrumb mb-0 py-0" itemscope itemtype="https://schema.org/BreadcrumbList">
		<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="/" itemprop="item" rel="canonical"><span itemprop="name">Home</span></a>
			<meta itemprop="position" content="1">
		</li>
		<li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name"><?= h($label) ?></span>
			<meta itemprop="position" content="2">
		</li>
	</ol>
</nav>
