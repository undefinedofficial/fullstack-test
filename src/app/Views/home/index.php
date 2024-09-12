<?= $this->extend("layouts/default") ?>

<?= $this->section("content") ?>
<div class="container my-5">
    <div class="d-flex flex-column">
        <div class="col mt-5 mb-2">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
                <span class="text-muted text-left">Сортировать: </span>
                <select id="sort-by" class="custom-select my-2 my-md-0 mx-md-2">
                    <option value="name" selected>По id</option>
                    <option value="date">По дате</option>
                </select>
                <select id="sort-as" class="custom-select">
                    <option value="asc" selected>По возрастанию</option>
                    <option value="desc">По убыванию</option>
                </select>
            </div>
        </div>

        <div id="comments" class="row justify-content-between justify-content-md-center">

        </div>

        <nav class="mt-5 mx-auto" aria-label="Page navigation example">
            <ul class="pagination" id="pagination">
                <li id="page-prev" class="page-item disabled">
                    <a id="page-prev-btn" class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li id="page-next" class="page-item disabled">
                    <a id="page-next-btn" class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

        <hr class="my-4 w-100 bg-dark" />

        <form id="commentForm">
            <h3>Добавить комментарий</h3>
            <div id="server-message" class="form-group text-danger text-center">
            </div>
            <div class="form-group">
                <label for="name">Ваша Почта</label>
                <input type="email" class="form-control" id="name" placeholder="name@example.com" />
                <span class="invalid-feedback"></span>
            </div>
            <div class="form-group">
                <label for="text">Текст коментария</label>
                <textarea class="form-control" id="text" rows="3"></textarea>
                <span class="invalid-feedback"></span>
            </div>

            <div class="form-group">
                <label for="date">Дата создания</label>
                <input type="date" class="form-control" id="date" rows="3" />
                <span class="invalid-feedback"></span>
            </div>
            <button type="submit" class="btn btn-primary w-100">Создать</button>
        </form>
    </div>
</div>
<template id="commentTemplate">
    <div class="col-lg-4 my-1">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <a href="#" class="card-link"></a>
                    <span></span>
                </h5>
                <p class="card-text card-content">
                </p>
                <p class="card-text mt-auto"><small class="text-muted">Дата создания: <span class="card-date"></span>
                    </small> </p>
            </div>
            <button type="button" class="btn btn-danger btn-delete">Удалить</button>
        </div>
    </div>
</template>

<script>
    let sortBy = 'id';
    let sortAs = 'asc';

    async function getComments(page) {
        return fetch(`/api/comments?page=${page}&sort=${sortBy}&order=${sortAs}`).then((response) => {
            if (response.ok) {
                return response.json().then((data) => {
                    return data;
                });
            }

            throw new Error('Network error.');
        });
    }

    let page = 1;
    let totalComments = 0;
    async function renderComments(page) {
        // clear comments container.
        const commentsContainer = $('#comments');

        // get comments from api.
        const { comments, total } = await getComments(page);
        totalComments = total;

        commentsContainer.empty();
        comments.forEach(comment => {

            const commentTemplate = $('#commentTemplate').html();

            const $comment = $(commentTemplate);
            $comment.find('.card-link').attr('href', '/comment/' + comment.id);
            $comment.find('.card-link').text(`#${comment.id}`);
            $comment.find('.card-title span').text(comment.name);
            $comment.find('.card-content').text(comment.text);
            $comment.find('.card-date').text(new Date(comment.date).toISOString().split('T')[0].split('-').reverse().join('.'));

            $comment.find('.btn-delete').on('click', () =>
                fetch(`/api/comments/${comment.id}`, {
                    method: 'DELETE'
                }).then((response) => {
                    if (response.ok) {
                        totalComments--;
                        return paginationTo(page);
                    }

                    throw new Error('Network error .');
                })
            );

            commentsContainer.append($comment);
        });
    }

    async function renderPagination(pageStart, pageCount) {

        if (pageStart < 1)
            pageStart = 1;

        if (pageStart > pageCount)
            pageStart = pageCount;

        let pageEnd = pageStart + 4;

        if (pageEnd > pageCount) {
            pageEnd = pageCount;
            pageStart = pageCount - 4;
        }

        if (pageStart < 1)
            pageStart = 1;


        $('#page-prev')[page <= 1 ? 'addClass' : 'removeClass']('disabled');
        $('#page-next')[page >= pageCount ? 'addClass' : 'removeClass']('disabled');

        $('.page-number').remove();

        const pageElement = ({ disabled, onClick, index, content, active }) =>
            $(`<li class="page-item page-number ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"></li>`)
                .append(
                    $(`<a class="page-link" href="/#${index}">${content}</a>`)
                        .on('click', (ev) => {
                            ev.preventDefault();
                            onClick()
                        })
                );

        for (let i = pageStart; i <= pageEnd; i++) {
            $('#page-next').before(
                pageElement({
                    index: i,
                    content: i,
                    active: i === page,
                    onClick: () => paginationTo(i)
                })
            );
        }
    }

    function prevNextPagination() {
        $('#page-prev-btn').on('click', (ev) => {
            ev.preventDefault();
            paginationTo(page - 1);
        });

        $('#page-next-btn').on('click', (ev) => {
            ev.preventDefault();
            paginationTo(page + 1);
        });
    }

    async function paginationTo(toPage) {
        if (toPage < 1) toPage = 1;
        else if (toPage > Math.ceil(totalComments / 3)) toPage = Math.ceil(totalComments / 3);

        page = Math.max(1, Math.min(Math.ceil(totalComments / 3), toPage));
        await renderComments(page);

        // check pagination after update comments.
        const pageValid = Math.max(1, Math.min(Math.ceil(totalComments / 3), page));

        // if page not valid, redirect to valid page.
        if (pageValid !== page)
            return paginationTo(pageValid);

        // update pagination block.
        await renderPagination(page - 2, Math.ceil(totalComments / 3));
    }

    function subscribeSort() {
        $('#sort-by').on('change', () => {
            sortBy = $('#sort-by').val();
            paginationTo(page);
        });
        $('#sort-as').on('change', (ev) => {
            sortAs = $('#sort-as').val();
            paginationTo(page);
        });
    }


    function subscribeForm() {
        const isValid = validateForm({
            formId: 'commentForm',
            rules: {
                name: {
                    required: true,
                    email: true,
                    maxLength: 128
                },
                text: {
                    required: true,
                    minLength: 10
                },
                date: {
                    required: true,
                    date: true
                }
            },
            messages: {
                name: {
                    required: 'Пожалуйста, укажите вашу почту.',
                    email: 'Не корректная почта.',
                    maxLength: 'Слишком длинная почта.'
                },
                text: {
                    required: 'Пожалуйста, введите ваш комментарий.',
                    minLength: 'Комментарий должен содержать не менее 10 символов.'
                },
                date: {
                    required: 'Пожалуйста, введите дату.',
                    date: 'Неверная дата.'
                }
            },
            onSubmit: () => {
                $('#server-message').text("");

                const name = $('#name').val().trim().toLowerCase();
                const text = $('#text').val().trim();
                const date = $('#date').val();
                fetch('http://localhost/api/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        text: text,
                        date: date
                    })
                }).then((response) => {
                    if (response.ok) {

                        // clear form fields.
                        $('#name').val('');
                        $('#text').val('');
                        $('#date').val('');

                        totalComments++;
                        return paginationTo(page + 1);
                    }

                    throw new Error('Network error.');
                }).catch((err) => {
                    $('#server-message').text("Произошла ошибка. Пожалуйста, повторите позже.");
                    console.log(err)
                });
            }
        });
    }

    $(document).ready(() => {
        subscribeForm();
        subscribeSort()
        prevNextPagination();
        paginationTo(page);
    })

    /**
     * Simple form validation
     */
    const rulesPattern = {
        required: () => (value) => !!value && ((typeof value === 'string' || Array.isArray(value)) && value.length > 0),
        email: () => (value) => !!value && value.toLowerCase().match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/),
        date: () => (value) => !!value && value.match(/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/),
        minLength: (length) => (value) => !!value && value.length >= length,
        maxLength: (length) => (value) => !!value && value.length <= length,
        // min: (min) => (value) => !!value && value >= min,
        // max: (max) => (value) => !!value && value <= max,
        // repeat: (other) => (value) => value === $(`#${other}`).val()
    }
    const validateForm = ({ formId, rules, messages, onSubmit }) => {
        const $form = $(`#${formId}`);
        const fields = [];

        Object.entries(rules).forEach(([field, rules]) => {
            const $field = $form.find(`#${field}`);
            const patterns = Object.entries(rules).map(([rule, value]) => {
                if (typeof rulesPattern[rule] !== 'function') throw new Error(`Invalid rule: ${rule}`);
                return {
                    check: rulesPattern[rule](value),
                    message: messages[field][rule] ?? `Заполните поле ${rule}.`
                };
            });

            const onUpdate = () => {
                const value = $field.val().trim();

                const errIdx = patterns.findIndex((pattern) => !pattern.check(value));
                if (errIdx !== -1) {
                    $field.addClass('is-invalid');
                    $field.next('.invalid-feedback').text(patterns[errIdx].message);
                    return false;
                } else {
                    $field.removeClass('is-invalid');
                    $field.next('.invalid-feedback').text('');
                }

                return true;
            }
            $field.on('input', onUpdate);
            $field.on('change', onUpdate);

            fields.push({
                field: $field,
                onValid: onUpdate
            });
        })

        const validateForm = () => {
            let isValid = true;
            fields.forEach(({ onValid }) => {
                if (onValid()) return;
                isValid = false;
            });
            return isValid;
        }

        $form.on('submit', function (e) {
            e.preventDefault();
            if (!validateForm()) return;
            onSubmit(e);
        });
        return validateForm;
    }
</script>
<?= $this->endSection() ?>