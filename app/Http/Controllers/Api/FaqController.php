<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\TextMessageMail;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        try{
            $faqs = Faq::all();
            return response()->json($faqs, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
