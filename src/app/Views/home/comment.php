<?= $this->extend("layouts/default") ?>

<?= $this->section("content") ?>
<div class="container my-5">
    <div class="d-flex flex-column">
        <div class="col my-5">
            <div class="d-flex flex-row">
                <p class="text-muted">ID: &nbsp;</p>
                <a href="/comment/<?= $comment->id ?>" class="text-decoration-none">#<?= esc($comment->id) ?></a>
            </div>
            <h3 class="display-6">Имя: <?= esc($comment->name) ?></h3>
            <p class="lead">
                Текст:  <?= esc($comment->text) ?>
            </p>
            <div class="d-flex flex-row justify-content-end align-items-center">
                <p class="text-muted text-right">Дата создания: <?= date('d.m.Y', strtotime($comment->date))?></p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
