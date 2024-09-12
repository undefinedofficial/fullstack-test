<?php

namespace App\Controllers;
use App\Models\CommentModel;
use Config\Services;


class ApiController extends BaseController
{
    private CommentModel $commentModel;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
        $this->validator = Services::validation();
    }

    public function comments()
    {
        $page = $this->request->getGet('page');
        $sort = $this->request->getGet('sort') === 'date' ? 'date' : 'id';
        $direction = $this->request->getGet('order') === 'desc' ? 'DESC' : 'ASC';

        if (!$page || !is_numeric($page) || $page < 1)
            $page = 1;

        $offset = ($page - 1) * 3;
        $comments = $this->commentModel->getCommentsAndSort($offset, 3, $sort, $direction);


        return $this->response->setJSON([
            'comments' => $comments,
            'total' => $this->commentModel->countAllResults()
        ]);
    }

    public function commentById(int $id)
    {
        $comment = $this->commentModel->getComment($id);
        return $this->response->setJSON(['comment' => $comment]);
    }

    public function createComment()
    {
        $data = $this->request->getJSON(true);

        $rules = [
            'name' => 'required|max_length[254]|valid_email',
            'text' => 'required|min_length[10]',
            'date' => 'required|valid_date|min_length[10]|max_length[10]',
        ];
        if (!$this->validator->setRules($rules, $messages = [])->run($data))
            return $this->response->setJSON(['status' => 'error', 'code' => 400, 'result' => $this->validator->getErrors()]);

        $result = $this->commentModel->createComment($data['name'], $data['text'], $data['date']);
        if ($result)
            return $this->response->setJSON(['status' => 'ok', 'code' => 200, 'result' => $result]);

        $errors = $this->commentModel->errors();
        return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'code' => 400, 'result' => $errors]);
    }

    // public function updateComment(int $id, string $name, string $text, string $date)
    // {

    // }

    public function deleteComment(int $id)
    {
        $result = $this->commentModel->deleteComment($id);
        if ($result)
            return $this->response->setJSON(['status' => 'ok', 'code' => 200, 'result' => $result]);
        return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'code' => 404]);
    }
}
