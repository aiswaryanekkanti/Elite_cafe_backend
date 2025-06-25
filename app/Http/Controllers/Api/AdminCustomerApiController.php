<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerDetail;

class AdminCustomerApiController extends Controller
{
    public function index()
{
    $customers = CustomerDetail::all();
    return response()->json(['data' => $customers]);
}

}
