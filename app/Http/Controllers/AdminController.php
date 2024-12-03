<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use PDF;
use Dompdf\Dompdf;
use Dompdf\Options;


class AdminController extends Controller
{
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:admins,phone_number',
            'email' => 'required|email|unique:admins,email',
            'address' => 'required|string',
            'password' => 'required|string|min:8|confirmed',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the admin
        $admin = Admin::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'status'=>'active',

            // Hash the password
        ]);

        return response()->json([
            'message' => 'Admin registered successfully',
            'admin' => $admin,
        ], 201);
    }
     public function getProfile(Request $request)
 {
     $admin = $request->user();

     if (!$admin) {
         return response()->json([
             'message' => 'Unauthorized access.',
         ], 401);
     }

     return response()->json([
         'message' => 'Admin profile retrieved successfully.',
         'data' => $admin,
     ], 200);
 }

 public function login(Request $request)
{
    // Validate input
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Check if the admin exists
    $admin = Admin::where('email', $request->email)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        return response()->json([
            'message' => 'Invalid email or password',
        ], 401); // Unauthorized
    }

    // Check if the admin's status is active
    if ($admin->status !== 'active') {
        return response()->json([
            'message' => 'Your account is not active. Please contact the super admin.',
        ], 403); // Forbidden
    }

    // Generate a token for the admin
    $token = $admin->createToken('admin_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'admin' => $admin,
        'token' => $token,
    ]);
}


      public function createUser(Request $request)
{
    // Validate the request input
    $validated = $request->validate([
        'surname' => 'required|string|max:255',
        'firstname' => 'required|string|max:255',
        'othername' => 'nullable|string|max:255',
        'sex' => 'required|string',
        'marital_status' => 'required|string',
        'phoneNumber' => 'required|string|max:255',
        'localgovernment' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'occupation' => 'required|string|max:255',
        'shop_address' => 'required|string|max:255',
        'purpose' => 'required|string',
        'amount' => 'required|numeric',
        'bvn' => 'nullable|string|max:255',
        'nin' => 'nullable|string|max:255',
        'level_of_education' => 'nullable|in:Uneducated,Primary School,Secondary School,OND,HND,BSc,MSc,PhD',
        'is_disabled' => 'nullable|boolean',
        'comment' => 'nullable|string',
        'bank_account_number' => 'nullable|string|max:255',
        'account_name' => 'nullable|string|max:255',
        'bank_name' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'dob' => 'required|nullable',

    ]);

    // Handle image upload if provided
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('user_images', 'public');
    }

    // Get the authenticated admin's name
    $adminName = auth()->user()->first_name; // Assuming the admin is authenticated

    // Create the user record
    $user = User::create([
        'surname' => $validated['surname'],
        'firstname' => $validated['firstname'],
        'othername' => $validated['othername'] ?? null,
        'sex' => $validated['sex'],
        'marital_status' => $validated['marital_status'],
        'phoneNumber' => $validated['phoneNumber'],
        'localgovernment' => $validated['localgovernment'],
        'address' => $validated['address'],
        'occupation' => $validated['occupation'],
        'shop_address' => $validated['shop_address'],
        'purpose' => $validated['purpose'],
        'amount' => $validated['amount'],
        'bvn' => $validated['bvn'] ?? null,
        'nin' => $validated['nin'] ?? null,
        'level_of_education' => $validated['level_of_education'] ?? null,
        'is_disabled' => $validated['is_disabled'],
        'comment' => $validated['comment'] ?? null,
        'bank_account_number' => $validated['bank_account_number'] ?? null,
        'account_name' => $validated['account_name'] ?? null,
        'bank_name' => $validated['bank_name'] ?? null,
        'image' => $imagePath,  // Store the image path
        'created_by' => $adminName, // Store the admin's name
        'dob' => $validated['dob']
    ]);

    return response()->json([
        'message' => 'User created successfully',
        'user' => $user,
    ], 201);
}



    public function getUserProfile($id)
{
    try {
        $user = User::findOrFail($id);

        if ($user->image) {
            $user->image_url = url('storage/' . $user->image);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.'
        ], 404);
    }
}


public function getAllUsers(Request $request)
{
    // Get query parameters for searching and filtering
    $searchQuery = $request->query('search');      // Search keyword
    $year = $request->query('year');              // Filter by year
    $sex = $request->query('sex');                // Filter by sex
    $hasAccountNumber = $request->query('has_account_number'); // Filter by account number presence

    // Retrieve users with optional search and filters
    $users = User::when($searchQuery, function ($query, $searchQuery) {
            return $query->where(function ($q) use ($searchQuery) {
                $q->where('surname', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('firstname', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('othername', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('phoneNumber', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('localgovernment', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('address', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('occupation', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('shop_address', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('purpose', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('account_name', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('bank_name', 'LIKE', '%' . $searchQuery . '%');
            });
        })
        ->when($year, function ($query, $year) {
            return $query->whereYear('created_at', $year); // Filter by year
        })
        ->when($sex, function ($query, $sex) {
            return $query->where('sex', $sex); // Filter by sex
        })
        ->when(isset($hasAccountNumber), function ($query) use ($hasAccountNumber) {
            return $query->whereNotNull('bank_account_number', $hasAccountNumber ? true : false);
        })
        ->orderBy('created_at', 'desc') // Order by latest
        ->paginate(30); // Paginate results

    // Add full image URL if an image exists
    $users->getCollection()->transform(function ($user) {
        if ($user->image) {
            $user->image_url = url('storage/' . $user->image);
        }
        return $user;
    });

    return response()->json([
        'message' => 'Users retrieved successfully',
        'data' => $users,
    ], 200);
}

public function searchUser(Request $request)
{
    // Build a query with optional search filters
    $query = User::query();

    // Apply filters if present in the request
    if ($request->has('surname')) {
        $query->where('surname', 'LIKE', '%' . $request->surname . '%');
    }

    if ($request->has('firstname')) {
        $query->where('firstname', 'LIKE', '%' . $request->firstname . '%');
    }

    if ($request->has('othername')) {
        $query->where('othername', 'LIKE', '%' . $request->othername . '%');
    }

    if ($request->has('sex')) {
        $query->where('sex', $request->sex);
    }

    if ($request->has('marital_status')) {
        $query->where('marital_status', $request->marital_status);
    }

    if ($request->has('phoneNumber')) {
        $query->where('phoneNumber', 'LIKE', '%' . $request->phoneNumber . '%');
    }

    if ($request->has('localgovernment')) {
        $query->where('localgovernment', 'LIKE', '%' . $request->localgovernment . '%');
    }

    if ($request->has('address')) {
        $query->where('address', 'LIKE', '%' . $request->address . '%');
    }

    if ($request->has('occupation')) {
        $query->where('occupation', 'LIKE', '%' . $request->occupation . '%');
    }

    if ($request->has('shop_address')) {
        $query->where('shop_address', 'LIKE', '%' . $request->shop_address . '%');
    }

    if ($request->has('purpose')) {
        $query->where('purpose', 'LIKE', '%' . $request->purpose . '%');
    }

    if ($request->has('amount')) {
        $query->where('amount', $request->amount);
    }

    // Fetch results
    $users = $query->get();

    // Return response
    if ($users->isEmpty()) {
        return response()->json(['message' => 'No users found'], 404);
    }

    return response()->json([
        'message' => 'Users retrieved successfully',
        'data' => $users,
    ], 200);
}
public function getUsersCountByMonth($year = null)
{
    // If no year is passed, use the current year
    if (!$year) {
        $year = Carbon::now()->year;
    }

    // Query the users created in the specified year and group by month
    $userCounts = DB::table('users')
        ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
        ->whereYear('created_at', $year)
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy('month', 'asc')  // To sort by month (ascending)
        ->get();

    // Prepare the result to match the format expected in the response
    $months = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    // Fill the result array with months and counts
    $result = [];
    foreach ($months as $index => $month) {
        $count = $userCounts->firstWhere('month', $index + 1);
        $result[] = [
            'month' => $month,
            'count' => $count ? $count->count : 0,  // Default to 0 if no users for that month
        ];
    }

    return response()->json($result);
}
public function recordPayment(Request $request)
{
    // Validate the request
    $request->validate([
        'user_id' => 'required|exists:users,id', // Ensure user exists
        'requested_amount' => 'required|numeric|min:0', // Validate requested amount
        'paid_amount' => 'required|numeric|min:0', // Validate paid amount
    ]);

    $userId = $request->input('user_id');
    $requestedAmount = $request->input('requested_amount');
    $paidAmount = $request->input('paid_amount');

    // Find or create a payment record for the user
    $payment = Payment::firstOrCreate(
        ['user_id' => $userId],
        ['amount_due' => $requestedAmount, 'amount_paid' => 0, 'amount_remaining' => $requestedAmount]
    );

    // Update the payment record
    $payment->amount_paid += $paidAmount;
    $payment->amount_remaining = max(0, $payment->amount_due - $payment->amount_paid); // Ensure no negative values
    $payment->payment_status = $payment->amount_remaining === 0 ? 'Paid' : 'Paid';
    $payment->save();

    // Response
    return response()->json([
        'message' => 'Payment recorded successfully.',
        'payment' => $payment,
    ]);
}


public function getAllPayments(Request $request)
{
    // Get filters from the request
    $year = $request->input('year'); // e.g., 2024
    $paymentStatus = $request->input('payment_status'); // e.g., "Paid" or "Pending"
    $name = $request->input('name'); // e.g., "John Doe"

    // Query the payments table with filters
    $query = Payment::with('user'); // Use Eloquent relationships to include user details

    // Filter by year if provided
    if ($year) {
        $query->whereYear('created_at', $year);
    }

    // Filter by payment status if provided
    if ($paymentStatus) {
        $query->where('payment_status', $paymentStatus);
    }

    // Search by user name if provided
    if ($name) {
        $query->whereHas('user', function ($q) use ($name) {
            $q->where('firstname', 'like', "%$name%")
              ->orWhere('surname', 'like', "%$name%")
              ->orWhere('othername', 'like', "%$name%");
        });
    }

    // Execute the query and paginate results
    $payments = $query->paginate(10);

    // Return the results as JSON
    return response()->json([
        'message' => 'Payments retrieved successfully.',
        'payments' => $payments,
    ]);
}

public function getMonthlyPaymentCounts(Request $request)
{
    // Get the year from the request or use the current year by default
    $year = $request->input('year', Carbon::now()->year);

    // Fetch the monthly counts of payments for the specified year
    $monthlyCounts = Payment::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', $year)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    // Format the data into the desired structure
    $volumeData = [];
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March',
        4 => 'April', 5 => 'May', 6 => 'June',
        7 => 'July', 8 => 'August', 9 => 'September',
        10 => 'October', 11 => 'November', 12 => 'December'
    ];

    foreach ($months as $monthNumber => $monthName) {
        $count = $monthlyCounts->firstWhere('month', $monthNumber)->count ?? 0;
        $volumeData[] = [
            'month' => $monthName,
            'count' => $count,
        ];
    }

    // Return the data as JSON
    return response()->json([
        'message' => 'Monthly payment counts retrieved successfully.',
        'year' => $year,
        'volumeData' => $volumeData,
    ]);
}

public function getDashboardStats()
{
    // Total amount paid
    $totalAmountPaid = Payment::sum('amount_paid');

    // Total number of users
    $totalUsers = User::count();

    // Total number of payment records submitted
    $totalRecordPayments = Payment::count();

    // Return data as JSON
    return response()->json([
        'message' => 'Dashboard statistics retrieved successfully.',
        'data' => [
            'total_amount_paid' => $totalAmountPaid,
            'total_users' => $totalUsers,
            'total_record_payments' => $totalRecordPayments,
        ],
    ]);
}
public function getAllRegisteredAdmins(Request $request)
{
    $searchableFields = ['name', 'email', 'phone', 'role'];

    $searchQuery = $request->query('search');

    $admins = Admin::when($searchQuery, function ($query, $searchQuery) use ($searchableFields) {
        $query->where(function ($subQuery) use ($searchableFields, $searchQuery) {
            foreach ($searchableFields as $field) {
                $subQuery->orWhere($field, 'LIKE', '%' . $searchQuery . '%');
            }
        });
    })
    ->orderBy('created_at', 'desc')
    ->paginate(50);

    return response()->json([
        'message' => 'Admins retrieved successfully.',
        'data' => $admins,
    ]);
}


public function updateAdminStatus(Request $request)
    {
        $request->validate([
            'admin_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $admin = Admin::find($value);
                    if (!$admin) {
                        $fail('The selected admin id is invalid.');
                    }
                },
            ],
            'status' => 'required|in:active,disabled',
        ]);

        $currentUser = Auth::user();

        if ($currentUser->email !== 'borlerjy@gmail.com') {
            return response()->json([
                'message' => 'Unauthorized action. Only the super admin can perform this action.',
            ], 403);
        }

        $targetAdmin = Admin::find($request->admin_id);

        if ($targetAdmin->email === 'borlerjy@gmail.com') {
            return response()->json([
                'message' => 'You cannot change your own status.',
            ], 400);
        }

        $targetAdmin->status = $request->status;
        $targetAdmin->save();

        return response()->json([
            'message' => 'Admin status updated successfully.',
            'admin' => [
                'id' => $targetAdmin->id,
                'name' => $targetAdmin->name,
                'status' => $targetAdmin->status,
            ],
        ], 200);
    }


    public function downloadUsersPDF(Request $request)
    {
        $query = User::query();
        if ($request->has('gender')) {
            $query->where('sex', $request->gender);
        }
        if ($request->has('account_number')) {
            $query->where('account_number', $request->account_number);
        }

        $users = $query->get();

        $html = view('pdf.users', compact('users'))->render();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return PDF file as download
        return response()->streamDownload(
            fn () => print($dompdf->output()),
            'users.pdf'
        );
    }






}