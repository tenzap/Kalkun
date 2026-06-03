<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
if ($pager->getPageCount() > 1) :
?>

<nav aria-label="<?= tr('Pager.pageNavigation') ?>">
	<ul class="pagination">
		<?php if ($pager->hasPrevious()) : ?>
			<li>
				<a href="<?= $pager->getFirst() ?>" aria-label="<?= tr('Pager.first') ?>">
					<span aria-hidden="true"><?= tr('Pager.first') ?></span>
				</a>
			</li>
			<li>
				<a href="<?= $pager->getPrevious() ?>" aria-label="<?= tr('Pager.previous') ?>">
					<span aria-hidden="true"><?= tr('Pager.previous') ?></span>
				</a>
			</li>
		<?php endif ?>

		<?php foreach ($pager->links() as $link) : ?>
			<li <?= $link['active'] ? 'class="current_page"' : '' ?>>
				<a href="<?= $link['uri'] ?>">
					<?= $link['title'] ?>
				</a>
			</li>
		<?php endforeach ?>

		<?php if ($pager->hasNext()) : ?>
			<li>
				<a href="<?= $pager->getNext() ?>" aria-label="<?= tr('Pager.next') ?>">
					<span aria-hidden="true"><?= tr('Pager.next') ?></span>
				</a>
			</li>
			<li>
				<a href="<?= $pager->getLast() ?>" aria-label="<?= tr('Pager.last') ?>">
					<span aria-hidden="true"><?= tr('Pager.last') ?></span>
				</a>
			</li>
		<?php endif ?>
	</ul>
</nav>
<?php endif; ?>
