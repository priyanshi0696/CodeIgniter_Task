<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class RegisterController extends Controller
{
    public function index()
    {
        return view('register_form.html');
    }

    public function create()
    {
        // Load validation library
        helper(['form', 'url']);
        $validation = \Config\Services::validation();

        // Define validation rules
        $validation->setRules([
            'first_name' => 'required|min_length[3]|max_length[50]',
            'last_name'  => 'required|min_length[3]|max_length[50]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[8]',
            'user_type'  => 'required'
        ]);

        // Check if the form validation is successful
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Return validation errors as JSON
            return $this->response->setJSON([
                'success' => false,
                'message' => $validation->getErrors()
            ]);
        }

        // Get form data
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT), // Secure password hash
            'user_type'  => $this->request->getPost('user_type')
        ];

        // Insert the data into the database
        $userModel = new \App\Models\UserModel();
        if ($userModel->save($data)) {
            // Respond with success
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registration successful!'
            ]);
        } else {
            // Respond with failure
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while registering. Please try again.'
            ]);
        }
    }
}

