<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Show customers and the add-customer form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $customers = Customer::query()
            ->orderByDesc('id')
            ->get(['id', 'name', 'email', 'created_at']);

        return view('customers.index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Store a new customer with a unique email and hashed password.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();

        Customer::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect('/customers')->with('status', 'Customer created.');
    }
}
