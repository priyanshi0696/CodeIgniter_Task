<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class LoginController extends Controller
{
    public function index()
    {
        return view('login.html');
    }

    public function login()
    {
        // Check if the request is AJAX and validate input
        if ($this->request->isAJAX()) {
            // Form validation for email and password
            $validation = \Config\Services::validation();

            $validation->setRules([
                'email' => 'required|valid_email',
                'password' => 'required', // Minimum length for password
            ]);

            if (!$validation->run($this->request->getPost())) {
                // Validation failed, return errors as JSON
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $validation->getErrors(),
                ]);
            }

            // Proceed with user authentication
            $model = new UserModel();
            $email = $this->request->getVar('email');
            $password = $this->request->getVar('password');

            // Fetch user data from database by email
            $user = $model->where('email', $email)->first();

            // Check if user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Set session data
                session()->set('user_id', $user['id']);
                session()->set('user_type', $user['user_type']);
                session()->set('is_logged_in', true);

                // Return success response
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful!',
                ]);
            } else {
                // Invalid credentials, return error
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ]);
            }
        }

        // If not AJAX request, show the login page
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
