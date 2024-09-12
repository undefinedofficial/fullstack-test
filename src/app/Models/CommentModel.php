<?php 

namespace App\Models;
use CodeIgniter\Model;

class CommentModel extends Model
{

    protected $table = 'comments';

    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'text', 'date'];

    protected $validationRules = [
        'name' => 'required|max_length[254]|valid_email',
        'text' => 'required|max_length[255]|min_length[10]',
        'date' => 'required|date',
    ];

    // protected $

    public function getCommentsAndSortById(int $offset = 0, int $limit = null)
    {
        $builder = $this->db->table($this->table);
        $builder->offset($offset);
        $builder->limit($limit);
        return $builder->get()->getResult();
    }

    public function countAllResultsAndSortById()
    {
        $builder = $this->db->table($this->table);
        
        $builder->orderBy('id', 'ASC');
        return $builder->countAllResults();
    }
    
    public function getCommentsAndSort(int $offset = 0, int $limit = null, string $sort = null, string $direction = null)
    {
        $builder = $this->db->table($this->table);

        $builder->offset($offset);
        $builder->limit($limit);

        $builder->orderBy($sort, $direction);
        return $builder->get()->getResult();
    }

    public function countAllResultsAndSortByDate()
    {
        $builder = $this->db->table($this->table);
        
        $builder->orderBy('date', 'ASC');
        return $builder->countAllResults();
    }

    public function getComment(int $id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('id', $id);
        return $builder->get()->getRow();
    }

    public function createComment(string $name, string $text, string $date)
    {
        $builder = $this->db->table($this->table);
        return $builder->insert(['name' => $name, 'text' => $text, 'date' => $date]);
    }

    // public function updateComment($id, $name, $text, $date)
    // {
    //     $builder = $this->db->table($this->table);
    //     $builder->where('id', $id);
    //     return $builder->update(['name' => $name, 'text' => $text, 'date' => $date]);
    // }

    public function deleteComment(int $id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('id', $id);
        return $builder->delete();
    }
}