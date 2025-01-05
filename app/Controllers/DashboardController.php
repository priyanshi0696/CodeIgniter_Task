<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch logged-in user's details
        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));
        
        // Check if the user exists
        if (!$user) {
            return redirect()->to('/login.html'); // Redirect to login if no user found
        }
    
        // Get user type and user ID from session
        $userType = $user['user_type']; // Example: 'dealer' or 'employee'
    
        // Initialize $dealers as an empty array to avoid undefined variable errors
        $dealers = [];
        $pager = null; // Set pager to null initially
    
        // If Dealer, check if City, State, and Zip code are filled
        if ($userType === 'dealer') {
            // Check if City, State, and Zip code are empty
            if (empty($user['city']) || empty($user['state']) || empty($user['zip_code'])) {
                // If any are missing, show the profile form for the dealer to fill out
                return view('dashboard.html', [
                    'user' => $user,  // Pass user data as an array
                    'user_type' => $userType,
                    'profile_incomplete' => true, // Flag to show the profile form
                    'dealers' => $dealers,
                    'pager' => $pager // Pass pager as null if no pagination is needed
                ]);
            }
        }
    
        // If Employee, fetch Dealer data for listing
        if ($userType === 'employee') {
            // Get page and perPage from the request
            $page = $this->request->getVar('page') ?? 1;  // Default to page 1 if no page is specified
            $perPage = $this->request->getVar('perPage') ?? 5;
    
            // Fetch dealers with pagination
            $dealers = $userModel->where('user_type', 'dealer')->paginate($perPage);
            $pager = $userModel->pager; // Initialize pager after fetching data
        }
    
        // If it's an AJAX request, return JSON data
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'dealers' => $dealers,
                'pagination' => [
                    'total_pages' => $pager->getPageCount(),
                    'current_page' => $pager->getCurrentPage(),
                    'links' => $pager->links('default', 'bootstrap5_full')
                ]
            ]);
        }
    
        // Pass data to the view
        return view('dashboard.html', [
            'user' => $user,
            'user_type' => $userType,
            'dealers' => $dealers,
            'pager' => $pager ? [
                'total_pages' => $pager->getPageCount(),
                'current_page' => $pager->getCurrentPage(),
                'links' => $pager->links('default', 'bootstrap5_full')
            ] : null // Only pass pager data if it's not null
        ]);
    }
    

    // Update Dealer's Profile
    public function updateDealerProfile()
    {
        $userModel = new UserModel();

        $userId = $this->request->getPost('user_id');
        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');
        $zipCode = $this->request->getPost('zip_code');

        // Server-side validation rules
        $validation = \Config\Services::validation();
        $validation->setRules([
            'city' => 'required|alpha_space|min_length[2]|max_length[50]',
            'state' => 'required|alpha_space|min_length[2]|max_length[50]',
            'zip_code' => 'required|exact_length[6]|numeric'
        ]);

        // Validate input
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $validation->getErrors()
            ]);
        }

        $data = [
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code')
        ];

        $userModel->update($userId, $data);
        return $this->response->setJSON(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    // Search Dealers by Zip code
    public function searchDealers()
    {
        $zip = $this->request->getPost('zip_code');
        $userModel = new UserModel();
        $dealers = $userModel->where('user_type', 'dealer')
            ->like('zip_code', $zip)
            ->findAll();

        return $this->response->setJSON(['success' => true, 'data' => $dealers]);
    }


    //edit dealer

    public function editDealer()
    {
        $userModel = new UserModel();

        $dealerId = $this->request->getPost('id');

        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');
        $zipCode = $this->request->getPost('zip_code');




        // Server-side validation rules
        $validation = \Config\Services::validation();
        $validation->setRules([
            'city' => 'required|alpha_space|min_length[2]|max_length[50]',
            'state' => 'required|alpha_space|min_length[2]|max_length[50]',
            'zip_code' => 'required|exact_length[6]|numeric'
        ]);

        // Validate input
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $validation->getErrors()
            ]);
        }

        $data = [
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code')
        ];

        $userModel->update($dealerId, $data);
        return $this->response->setJSON(['success' => true, 'message' => 'Dealer updated successfully.']);
    }

    public function getDealer($id)
    {

        // Check if the ID is valid
        if (!is_numeric($id)) {
            return $this->response->setStatusCode(400, 'Bad Request')->setBody('Invalid dealer ID.');
        }
        $userModel = new UserModel();
        $dealer = $userModel->find($id);

        // Check if dealer exists
        if (!$dealer) {
            return $this->response->setStatusCode(404, 'Not Found')->setBody('Dealer not found.');
        }

        return $this->response->setJSON(['success' => true, 'data' => $dealer]);
    }


    public function deleteDealer()
    {
        // Load the UserModel
        $userModel = new UserModel();

        // Get the dealer ID from the POST request
        $dealerId = $this->request->getPost('id');

        // Check if the dealer ID is valid
        if (!$dealerId) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'Invalid dealer ID.']);
        }

        try {
            // Attempt to delete the dealer by ID
            if ($userModel->delete($dealerId)) {
                return $this->response->setStatusCode(200)
                    ->setJSON(['success' => true, 'message' => 'Dealer deleted successfully.']);
            } else {
                return $this->response->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => 'Failed to delete dealer.']);
            }
        } catch (\Exception $e) {
            // Handle exceptions such as foreign key constraints
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Error deleting dealer: ' . $e->getMessage()]);
        }
    }
}
