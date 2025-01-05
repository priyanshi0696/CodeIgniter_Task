<?php
namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['first_name', 'last_name', 'email', 'password', 'user_type', 'city', 'state', 'zip_code'];
    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email]',
    ];
}
