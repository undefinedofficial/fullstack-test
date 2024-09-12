<?php

namespace App\Controllers;
use App\Models\CommentModel;

class HomeController extends BaseController
{
    private CommentModel $commentModel;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
    }

    public function index()
    {
        return view('home/index', [
            'title' => 'Hello',
        ]);
    }

    public function comment(int $id)
    {
        $comment = $this->commentModel->getComment($id);
        if ($comment) 
            return view('home/comment', [
                'title' => 'Comment ' . $id,
                'comment' => $comment,
            ]);
        
        return view('errors/html/error_404');
    }
}
