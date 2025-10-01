<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- أضف هذا
use Illuminate\Foundation\Validation\ValidatesRequests;   // <-- أضف هذا
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // --- هذا هو السطر الأهم ---
    use AuthorizesRequests, ValidatesRequests;
}
